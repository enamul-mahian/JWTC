<?php

namespace App\Http\Controllers\Secure;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\Jobseeker\PrivateDocumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApplicantDocumentWebController extends Controller
{
    private function applicant(Request $request): void
    {
        abort_unless($request->user() && $request->user()->user_type === UserType::JOB_SEEKER, 403);
        abort_unless(Schema::hasTable('applicant_private_documents'), 503);
    }

    public function index(Request $request)
    {
        $this->applicant($request);
        return response()->view('secure.documents.applicant_index', [
            'documents' => DB::table('applicant_private_documents')
                ->where('user_id', $request->user()->id)
                ->orderByDesc('id')->paginate(12),
        ]);
    }

    public function store(Request $request, PrivateDocumentController $api)
    {
        $this->applicant($request);
        $api->upload($request);

        return redirect()->route('jobseeker.private-documents.index')
            ->with('success', 'Your document was uploaded securely for review.');
    }

    public function revise(Request $request, int $id, PrivateDocumentController $api)
    {
        $this->applicant($request);
        $api->revise($request, $id);

        return redirect()->route('jobseeker.private-documents.show', $id)
            ->with('success', 'Your new revision was submitted for review.');
    }

    public function show(Request $request, int $id)
    {
        $this->applicant($request);
        $document = DB::table('applicant_private_documents')
            ->where('id', $id)->where('user_id', $request->user()->id)->first();
        abort_unless($document, 404);

        return response()->view('secure.documents.applicant_show', [
            'document' => $document,
            'versions' => DB::table('applicant_private_document_versions')
                ->where('document_id', $id)->where('user_id', $request->user()->id)
                ->orderByDesc('version')->get(),
            'events' => DB::table('applicant_private_document_events')
                ->where('document_id', $id)->orderByDesc('id')->get(),
        ]);
    }

    public function download(Request $request, int $id, int $version, PrivateDocumentController $api)
    {
        $this->applicant($request);
        return $api->download($request, $id, $version);
    }
}
