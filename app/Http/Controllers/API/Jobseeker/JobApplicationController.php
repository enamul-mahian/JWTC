<?php

namespace App\Http\Controllers\API\Jobseeker;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Mail\ResumeMail;
use App\Models\Application;
use App\Models\CoverLetter;
use App\Models\EmployerCvView;
use App\Models\Job;
use App\Models\JobseekerCertificate;
use App\Models\JobseekerUploads;
use App\Models\PersonalInformation;
use App\Models\UploadedCv;
use App\Services\ApplicationSubmissionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobApplicationController extends Controller
{
    private function applicant(Request $request): void
    {
        abort_unless($request->user() && $request->user()->user_type === UserType::JOB_SEEKER,
            403, 'Job seeker account required.');
    }

    public function jobSearch(Request $request)
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:100'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
        ]);

        $query = Job::with(['company', 'country'])
            ->where('is_publish', 1)
            ->where(function ($q) {
                $q->whereNull('application_deadline')
                    ->orWhereDate('application_deadline', '>=', today());
            });

        $search = $data['search'] ?? $data['title'] ?? null;
        if ($search) {
            $query->where('title', 'like', '%' . addcslashes($search, '%_') . '%');
        }
        if (isset($data['country_id'])) {
            $query->where('country_id', $data['country_id']);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function resumeViewd(Request $request)
    {
        $this->applicant($request);
        return response()->json([
            'views' => (int) EmployerCvView::where('user_id', $request->user()->id)->sum('count'),
        ]);
    }

    public function applyJob(Request $request, ApplicationSubmissionService $submissions)
    {
        $this->applicant($request);
        $data = $request->validate([
            'job_id' => ['required', 'integer', 'exists:jobs,id'],
            'expected_salary' => ['required', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        try {
            $application = $submissions->submit(
                $request->user(),
                Job::findOrFail($data['job_id']),
                (float) $data['expected_salary']
            );
        } catch (\DomainException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'Application submitted.', 'application' => $application], 201);
    }

    public function jobDetails(Request $request, int $id)
    {
        $this->applicant($request);
        return response()->json(
            Job::with(['company', 'country', 'job_category', 'degree_level', 'degree_name'])
                ->where('is_publish', 1)->findOrFail($id)
        );
    }

    public function viewJobseekerProfile(Request $request)
    {
        $this->applicant($request);
        $user = $request->user()->load([
            'personal_info', 'work_experiences', 'trainings', 'skills', 'languages',
            'educations', 'applications'
        ]);

        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'phone', 'user_type']),
            'personal_information' => $user->personal_info,
            'work_experiences' => $user->work_experiences,
            'trainings' => $user->trainings,
            'skills' => $user->skills,
            'languages' => $user->languages,
            'educations' => $user->educations,
            'applications' => $user->applications,
        ]);
    }

    public function saveProfileInfo(Request $request)
    {
        $this->applicant($request);
        $data = $request->validate([
            'first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'about_me' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'dob' => ['sometimes', 'nullable', 'date'],
            'gender' => ['sometimes', 'nullable', 'string', 'max:50'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:100'],
            'alternate_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'alternate_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'present_address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'present_city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'present_country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'permanent_address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'permanent_city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'permanent_country' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $record = PersonalInformation::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return response()->json(['message' => 'Profile saved.', 'profile' => $record]);
    }

    public function uploadPhoto(Request $request)
    {
        $this->applicant($request);
        $data = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $file = $data['photo'];
        $path = $file->store('profile-photos/' . $request->user()->id, 'public');
        $request->user()->forceFill(['photo' => $path])->save();

        return response()->json(['message' => 'Profile photo uploaded.', 'path' => $path], 201);
    }

    private function uploadDocument(Request $request, string $field, string $subdirectory, string $model)
    {
        $this->applicant($request);
        $data = $request->validate([
            $field => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:20480'],
        ]);
        $file = $data[$field];
        $path = $file->store('private/applicants/' . $request->user()->id . '/' . $subdirectory, 'local');
        $document = $model::create([
            'user_id' => $request->user()->id,
            'title' => Str::limit($file->getClientOriginalName(), 255, ''),
            'file' => $path,
        ]);
        return response()->json([
            'message' => 'Document uploaded securely.',
            'document' => $document->only(['id', 'title', 'created_at']),
        ], 201);
    }

    public function uploadCv(Request $request)
    {
        return $this->uploadDocument($request, 'resume', 'cv', UploadedCv::class);
    }

    public function storeCertificate(Request $request)
    {
        return $this->uploadDocument($request, 'certificate', 'certificates', JobseekerCertificate::class);
    }

    public function jobseekerDocUpload(Request $request)
    {
        return $this->uploadDocument($request, 'upload', 'documents', JobseekerUploads::class);
    }

    public function generatePDF(Request $request)
    {
        $this->applicant($request);
        $user = $request->user()->load([
            'personal_info', 'work_experiences', 'trainings', 'skills',
            'languages', 'educations'
        ]);
        return Pdf::loadView('resume.resume', ['user' => $user])->download('resume.pdf');
    }

    public function coverLetter(Request $request)
    {
        $this->applicant($request);
        return response()->json(CoverLetter::where('user_id', $request->user()->id)->first());
    }

    private function saveCover(Request $request, array $rules)
    {
        $this->applicant($request);
        $data = $request->validate($rules);
        $record = CoverLetter::updateOrCreate(['user_id' => $request->user()->id], $data);
        return response()->json(['message' => 'Cover letter saved.', 'cover_letter' => $record]);
    }

    public function savePersonInfo(Request $request)
    {
        return $this->saveCover($request, [
            'first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'your_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);
    }

    public function saveContactInfo(Request $request)
    {
        return $this->saveCover($request, [
            'organization' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_line_1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_line_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'date' => ['sometimes', 'nullable', 'date'],
        ]);
    }

    public function saveContentInfo(Request $request)
    {
        return $this->saveCover($request, [
            'subject' => ['sometimes', 'nullable', 'string', 'max:255'],
            'content' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'openin_salutation' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);
    }

    public function saveClosingInfo(Request $request)
    {
        return $this->saveCover($request, [
            'closing_salutation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'opening_last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'your_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);
    }

    public function exportToPDF(Request $request)
    {
        $this->applicant($request);
        $user = $request->user()->load('cover_letter');
        abort_unless($user->cover_letter, 404, 'Cover letter not found.');
        return Pdf::loadView('jobseeker.cover.cover_pdf', ['user' => $user])
            ->download('cover-letter.pdf');
    }

    public function exportToWord(Request $request)
    {
        $this->applicant($request);
        $letter = CoverLetter::where('user_id', $request->user()->id)->firstOrFail();
        $word = new \PhpOffice\PhpWord\PhpWord();
        $section = $word->addSection();
        $section->addText((string) $letter->subject);
        $section->addText((string) $letter->organization);
        $section->addText(strip_tags((string) $letter->content));
        $section->addText((string) $letter->closing_salutation);
        $section->addText((string) $letter->your_name);

        $tempFile = tempnam(sys_get_temp_dir(), 'jwtc-word-');
        try {
            $word->save($tempFile, 'Word2007');
            return response()->download($tempFile, 'cover-letter.docx')
                ->deleteFileAfterSend(true);
        } catch (\Throwable $exception) {
            @unlink($tempFile);
            throw $exception;
        }
    }

    public function applicationTimeline(Request $request, int $id)
    {
        $this->applicant($request);
        $application = Application::where('user_id', $request->user()->id)
            ->with('job:id,title,company_id')
            ->findOrFail($id);

        $catalog = app(\App\Services\ApplicationStatusCatalog::class);
        $history = \Illuminate\Support\Facades\Schema::hasTable('application_status_events')
            ? \Illuminate\Support\Facades\DB::table('application_status_events')
                ->where('application_id', $application->id)
                ->orderBy('created_at')->orderBy('id')
                ->get(['from_status_id', 'to_status_id', 'status_name', 'progress',
                    'source', 'note', 'created_at'])
            : collect();

        return response()->json([
            'application' => $application,
            'status' => $catalog->find((int) $application->status),
            'stages' => $catalog->all(),
            'history' => $history,
            'history_note' => 'Older status changes may predate the audit timeline.',
        ]);
    }

    public function getAppliedJob(Request $request)
    {
        $this->applicant($request);
        return response()->json(Application::with('job')
            ->where('user_id', $request->user()->id)
            ->latest('id')->paginate(15));
    }

    public function getShortlistedJob(Request $request)
    {
        $this->applicant($request);
        return response()->json(Application::with('job')
            ->where('user_id', $request->user()->id)
            ->where('status', 2)
            ->latest('id')->paginate(15));
    }

    public function sendResume(Request $request)
    {
        $this->applicant($request);
        $data = $request->validate([
            'txtCompanyEmail' => ['required', 'email', 'max:255'],
            'txtMailSubject' => ['required', 'string', 'max:255'],
            'txtApplication' => ['required', 'string', 'max:3000'],
            'attachment' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $mail = new ResumeMail([
            'name' => $request->user()->name,
            'subject' => $data['txtMailSubject'],
            'message' => $data['txtApplication'],
            'attachment' => null,
        ]);
        $mail->subject($data['txtMailSubject']);
        $mail->attachData(
            file_get_contents($data['attachment']->getRealPath()),
            $data['attachment']->getClientOriginalName(),
            ['mime' => $data['attachment']->getMimeType()]
        );
        Mail::to($data['txtCompanyEmail'])->send($mail);
        return response()->json(['message' => 'Resume sent.']);
    }
}
