<?php

namespace App\Http\Controllers\API\Jobseeker;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrivateDocumentController extends Controller
{
    private function applicant(Request $request): User
    {
        $user = $request->user();
        abort_unless($user && $user->user_type === UserType::JOB_SEEKER, 403);
        return $user;
    }

    private function document(User $user, int $id): object
    {
        $document = DB::table('applicant_private_documents')
            ->where('id', $id)->where('user_id', $user->id)->first();
        abort_unless($document, 404);

        return $document;
    }

    private function attachmentRules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    private function assertUniqueFile(User $user, string $checksum): void
    {
        if (DB::table('applicant_private_document_versions')
            ->where('user_id', $user->id)->where('sha256', $checksum)->exists()) {
            throw ValidationException::withMessages([
                'file' => 'You have already uploaded this document.',
            ]);
        }
    }

    private function storeRevision(
        Request $request, UploadedFile $file, string $checksum,
        ?int $documentId, string $type, string $title
    ): array {
        $user = $this->applicant($request);
        $this->assertUniqueFile($user, $checksum);
        $path = $file->store('private-documents/' . $user->id, 'local');

        if (!$path) {
            throw ValidationException::withMessages([
                'file' => 'The private document could not be stored.',
            ]);
        }

        try {
            return DB::transaction(function () use ($user, $file, $checksum, $documentId, $type, $title, $path) {
                // Locking the applicant row serializes both first-passport uploads and revisions.
                User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $this->assertUniqueFile($user, $checksum);

                $now = now();
                if ($documentId === null) {
                    if ($type === 'passport' && DB::table('applicant_private_documents')
                        ->where('user_id', $user->id)->where('type', 'passport')->exists()) {
                        throw ValidationException::withMessages([
                            'type' => 'A passport record already exists. Submit a revision instead.',
                        ]);
                    }

                    $documentId = DB::table('applicant_private_documents')->insertGetId([
                        'user_id' => $user->id, 'type' => $type, 'title' => $title,
                        'status' => 'pending_review', 'created_at' => $now, 'updated_at' => $now,
                    ]);
                    $version = 1;
                } else {
                    $document = DB::table('applicant_private_documents')
                        ->where('id', $documentId)->where('user_id', $user->id)
                        ->lockForUpdate()->first();
                    abort_unless($document, 404);
                    if ($document->locked_at !== null) {
                        throw ValidationException::withMessages([
                            'file' => 'This document is locked. Contact support to request a revision.',
                        ]);
                    }

                    $version = 1 + (int) DB::table('applicant_private_document_versions')
                        ->where('document_id', $documentId)->max('version');

                    DB::table('applicant_private_documents')->where('id', $documentId)->update([
                        'title' => $title, 'status' => 'pending_review', 'updated_at' => $now,
                    ]);
                }

                DB::table('applicant_private_document_versions')->insert([
                    'document_id' => $documentId, 'user_id' => $user->id,
                    'version' => $version, 'disk' => 'local', 'path' => $path,
                    'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 180),
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'file_size' => $file->getSize(), 'sha256' => $checksum,
                    'created_at' => $now,
                ]);

                DB::table('applicant_private_document_events')->insert([
                    'document_id' => $documentId, 'actor_id' => $user->id,
                    'action' => $version === 1 ? 'uploaded' : 'revised',
                    'old_status' => $version === 1 ? null : $document->status,
                    'new_status' => 'pending_review', 'version' => $version,
                    'created_at' => $now,
                ]);

                return ['id' => $documentId, 'version' => $version, 'status' => 'pending_review'];
            }, 3);
        } catch (\Throwable $exception) {
            // A failed DB transaction must not leave the uploaded private file orphaned.
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    public function index(Request $request)
    {
        $user = $this->applicant($request);
        $documents = DB::table('applicant_private_documents')
            ->where('user_id', $user->id)->orderByDesc('id')
            ->get(['id', 'type', 'title', 'status', 'locked_at', 'created_at', 'updated_at']);

        return response()->json(['documents' => $documents]);
    }

    public function upload(Request $request)
    {
        $user = $this->applicant($request);
        $data = $request->validate([
            'type' => ['required', 'in:passport,certificate,other'],
            'title' => ['required', 'string', 'min:2', 'max:180'],
        ] + $this->attachmentRules());

        $checksum = hash_file('sha256', $data['file']->getRealPath());
        $result = $this->storeRevision($request, $data['file'], $checksum, null,
            $data['type'], $data['title']);

        return response()->json([
            'message' => 'Document uploaded privately for review.', 'document' => $result,
        ], 201);
    }

    public function revise(Request $request, int $id)
    {
        $user = $this->applicant($request);
        $document = $this->document($user, $id);
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'min:2', 'max:180'],
        ] + $this->attachmentRules());

        $checksum = hash_file('sha256', $data['file']->getRealPath());
        $result = $this->storeRevision($request, $data['file'], $checksum, $id,
            $document->type, $data['title'] ?? $document->title);

        return response()->json(['message' => 'Revision submitted for review.', 'document' => $result], 201);
    }

    public function history(Request $request, int $id)
    {
        $user = $this->applicant($request);
        $document = $this->document($user, $id);

        $versions = DB::table('applicant_private_document_versions')
            ->where('document_id', $id)->orderByDesc('version')
            ->get(['id', 'version', 'original_name', 'mime_type', 'file_size', 'created_at']);
        $events = DB::table('applicant_private_document_events')
            ->where('document_id', $id)->orderBy('created_at')->orderBy('id')
            ->get(['action', 'old_status', 'new_status', 'version', 'reason', 'created_at']);

        return response()->json([
            'document' => [
                'id' => $document->id, 'type' => $document->type,
                'title' => $document->title, 'status' => $document->status,
                'locked' => $document->locked_at !== null,
            ], 'versions' => $versions, 'events' => $events,
        ]);
    }

    public function download(Request $request, int $id, int $version)
    {
        $user = $this->applicant($request);
        $this->document($user, $id);

        $record = DB::table('applicant_private_document_versions')
            ->where('document_id', $id)->where('user_id', $user->id)
            ->where('version', $version)->first();
        abort_unless($record, 404);
        abort_unless($record->disk === 'local' &&
            str_starts_with($record->path, 'private-documents/' . $user->id . '/'), 404);
        abort_unless(Storage::disk('local')->exists($record->path), 404);

        return Storage::disk('local')->download($record->path, $record->original_name);
    }
}
