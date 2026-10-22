<?php

namespace App\Http\Controllers\API\Admin;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PrivateDocumentReviewController extends Controller
{
    private function reviewer(Request $request): void
    {
        $user = $request->user();
        abort_unless($user &&
            in_array($user->user_type, [UserType::ADMIN, UserType::SUPER_ADMIN], true) &&
            $user->can('active-job'), 403);
    }

    public function show(Request $request, int $id)
    {
        $this->reviewer($request);
        $document = DB::table('applicant_private_documents')->where('id', $id)
            ->first();
        abort_unless($document, 404);

        $versions = DB::table('applicant_private_document_versions')
            ->where('document_id', $id)->orderByDesc('version')
            ->get(['version', 'original_name', 'mime_type', 'file_size', 'created_at']);
        $history = DB::table('applicant_private_document_events')
            ->where('document_id', $id)->orderBy('created_at')->orderBy('id')
            ->get(['action', 'actor_id', 'version', 'old_status',
                'new_status', 'reason', 'created_at']);

        // Never expose private storage paths or hash values in an API response.
        return response()->json([
            'document' => [
                'id' => $document->id, 'user_id' => $document->user_id,
                'type' => $document->type, 'title' => $document->title,
                'status' => $document->status,
                'locked' => $document->locked_at !== null,
            ],
            'versions' => $versions, 'history' => $history,
        ]);
    }

    public function download(Request $request, int $id, int $version)
    {
        $this->reviewer($request);
        $document = DB::table('applicant_private_documents')->where('id', $id)->first();
        abort_unless($document, 404);

        $file = DB::table('applicant_private_document_versions')
            ->where('document_id', $id)->where('user_id', $document->user_id)
            ->where('version', $version)->first();
        abort_unless($file && $file->disk === 'local', 404);
        abort_unless(str_starts_with($file->path,
            'private-documents/' . $document->user_id . '/'), 404);
        abort_unless(Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    public function decide(Request $request, int $id)
    {
        $this->reviewer($request);
        $data = $request->validate([
            'action' => ['required', 'in:lock,unlock,approve,request_revision,reject'],
            'reason' => ['required_if:action,lock,unlock,request_revision,reject',
                'nullable', 'string', 'min:3', 'max:1000'],
        ]);

        $result = DB::transaction(function () use ($request, $id, $data) {
            $document = DB::table('applicant_private_documents')
                ->where('id', $id)->lockForUpdate()->first();
            abort_unless($document, 404);

            $action = $data['action'];
            $locked = $document->locked_at !== null;
            $status = $document->status;

            if ($action === 'lock' && $locked) {
                throw ValidationException::withMessages(['action' => 'Document is already locked.']);
            }
            if ($action === 'unlock' && !$locked) {
                throw ValidationException::withMessages(['action' => 'Document is not locked.']);
            }
            if (in_array($action, ['approve', 'request_revision', 'reject'], true) && $locked) {
                throw ValidationException::withMessages([
                    'action' => 'Unlock the document before changing review status.',
                ]);
            }

            $now = now();
            $updates = ['updated_at' => $now];
            if ($action === 'lock') {
                $updates += [
                    'locked_at' => $now,
                    'locked_by' => $request->user()->id,
                    'lock_reason' => $data['reason'],
                ];
            } elseif ($action === 'unlock') {
                $updates += [
                    'locked_at' => null,
                    'locked_by' => null,
                    'lock_reason' => null,
                ];
            } else {
                $updates['status'] = match ($action) {
                    'approve' => 'approved',
                    'request_revision' => 'revision_requested',
                    default => 'rejected',
                };
            }

            DB::table('applicant_private_documents')->where('id', $id)->update($updates);
            DB::table('applicant_private_document_events')->insert([
                'document_id' => $id, 'actor_id' => $request->user()->id,
                'action' => $action, 'old_status' => $status,
                'new_status' => $updates['status'] ?? $status,
                'reason' => $data['reason'] ?? null, 'created_at' => $now,
            ]);

            return $updates['status'] ?? $status;
        }, 3);

        return response()->json([
            'message' => 'Document review decision recorded.',
            'status' => $result,
        ]);
    }
}
