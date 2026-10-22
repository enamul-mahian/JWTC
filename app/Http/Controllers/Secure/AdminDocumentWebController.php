<?php

namespace App\Http\Controllers\Secure;

use App\Enum\UserType;
use App\Http\Controllers\API\Admin\PrivateDocumentReviewController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDocumentWebController extends Controller
{
    private function reviewer(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && in_array($user->user_type, [
            UserType::ADMIN, UserType::SUPER_ADMIN
        ], true) && $user->can('active-job'), 403);
        abort_unless(Schema::hasTable('applicant_private_documents'), 503);
    }

    public function index(Request $request)
    {
        $this->reviewer($request);

        $data = $request->validate([
            'status' => ['nullable', 'in:pending_review,approved,revision_requested,rejected'],
        ]);

        $query = DB::table('applicant_private_documents')
            ->join('users', 'users.id', '=', 'applicant_private_documents.user_id')
            ->select('applicant_private_documents.*', 'users.name as applicant_name')
            ->orderByDesc('applicant_private_documents.updated_at');

        if (isset($data['status'])) {
            $query->where('applicant_private_documents.status', $data['status']);
        }

        return response()->view('secure.documents.admin_index', [
            'documents' => $query->paginate(15)->withQueryString(),
            'filter' => $data['status'] ?? '',
        ]);
    }

    public function show(Request $request, int $id)
    {
        $this->reviewer($request);
        $document = DB::table('applicant_private_documents')
            ->join('users', 'users.id', '=', 'applicant_private_documents.user_id')
            ->where('applicant_private_documents.id', $id)
            ->select('applicant_private_documents.*', 'users.name as applicant_name')
            ->first();
        abort_unless($document, 404);

        return response()->view('secure.documents.admin_show', [
            'document' => $document,
            'versions' => DB::table('applicant_private_document_versions')
                ->where('document_id', $id)->orderByDesc('version')->get(),
            'events' => DB::table('applicant_private_document_events')
                ->where('document_id', $id)->orderByDesc('id')->get(),
        ]);
    }

    public function decide(Request $request, int $id, PrivateDocumentReviewController $api)
    {
        $this->reviewer($request);
        $api->decide($request, $id);

        return redirect()->route('admin.private-documents.show', $id)
            ->with('success', 'Review decision saved in the audit history.');
    }

    public function download(Request $request, int $id, int $version, PrivateDocumentReviewController $api)
    {
        $this->reviewer($request);
        return $api->download($request, $id, $version);
    }
}
