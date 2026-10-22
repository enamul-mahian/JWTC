<?php

namespace App\Http\Controllers\API\Employer;

use App\Enum\JobStatus;
use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Dashboard\Company;
use App\Models\Job;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    private function employer(Request $request): void
    {
        abort_unless($request->user() && $request->user()->user_type === UserType::EMPLOYER,
            403, 'Employer account required.');
    }

    private function company(Request $request): Company
    {
        $this->employer($request);
        return Company::where('user_id', $request->user()->id)->firstOrFail();
    }

    private function job(Request $request, int $id): Job
    {
        $company = $this->company($request);
        return Job::withTrashed()
            ->where('user_id', $request->user()->id)
            ->where('company_id', $company->id)
            ->findOrFail($id);
    }

    public function jobAnalytics(Request $request)
    {
        $company = $this->company($request);
        $jobs = Job::withTrashed()->where('company_id', $company->id)
            ->where('user_id', $request->user()->id);

        return response()->json([
            'jobs_total' => (clone $jobs)->count(),
            'jobs_live' => (clone $jobs)->whereNull('deleted_at')->where('is_publish', 1)->count(),
            'jobs_archived' => (clone $jobs)->whereNotNull('deleted_at')->count(),
            'applicants_total' => Application::whereIn('job_id', (clone $jobs)->select('id'))->count(),
        ]);
    }

    public function jobList(Request $request)
    {
        $company = $this->company($request);
        return response()->json(Job::where('user_id', $request->user()->id)
            ->where('company_id', $company->id)->latest('id')->paginate(15));
    }

    public function jobDetails(Request $request, int $id)
    {
        return response()->json($this->job($request, $id)->load(['country', 'job_category']));
    }

    public function archiveJob(Request $request)
    {
        $company = $this->company($request);
        return response()->json(Job::onlyTrashed()
            ->where('user_id', $request->user()->id)
            ->where('company_id', $company->id)->latest('id')->paginate(15));
    }

    public function destroy(Request $request, int $id)
    {
        $job = $this->job($request, $id);
        abort_if($job->trashed(), 409, 'Job is already archived.');
        $job->delete();
        return response()->json(['message' => 'Job archived.']);
    }

    public function restoreJob(Request $request, int $id)
    {
        $job = $this->job($request, $id);
        abort_unless($job->trashed(), 409, 'Job is not archived.');
        $job->restore();
        return response()->json(['message' => 'Job restored.']);
    }

    private function jobRules(bool $creating): array
    {
        $required = $creating ? 'required' : 'sometimes';

        return [
            'title' => [$required, 'string', 'max:255'],
            'job_category_id' => [$required, 'integer', 'exists:job_categories,id'],
            'country_id' => [$required, 'integer', 'exists:countries,id'],
            'salary_type' => [$required, 'string', 'max:50'],
            'resume_receiving_email' => [$required, 'email', 'max:255'],
            'job_responsibilities' => [$required, 'string', 'max:10000'],
            'application_deadline' => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
            'minimum_salary' => [$required, 'integer', 'min:0'],
            'maximum_salary' => [$required, 'integer', 'min:0'],
            'vacancies' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'job_context' => ['sometimes', 'nullable', 'string', 'max:15000'],
            'additional_salary_info' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'additional_requirements' => ['sometimes', 'nullable', 'string', 'max:15000'],
            'degree_level_id' => ['sometimes', 'nullable', 'integer', 'exists:degree_lavels,id'],
            'degree_name_id' => ['sometimes', 'nullable', 'integer', 'exists:degree_names,id'],
            'salary_review' => ['sometimes', 'nullable', 'string', 'max:255'],
            'lunch_facilities' => ['sometimes', 'nullable', 'string', 'max:255'],
            'festival_bonus' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'mini_age' => ['sometimes', 'nullable', 'integer', 'min:16', 'max:100'],
            'max_age' => ['sometimes', 'nullable', 'integer', 'min:16', 'max:100'],
            'minimum_year' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'maximum_year' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'fresher' => ['sometimes', 'nullable', 'string', 'max:255'],
            'job_level' => ['sometimes', 'nullable', 'array', 'max:20'],
            'job_level.*' => ['string', 'max:100'],
            'employment_status' => ['sometimes', 'nullable', 'array', 'max:20'],
            'employment_status.*' => ['integer', 'exists:job_types,id'],
            'work_place' => ['sometimes', 'nullable', 'array', 'max:20'],
            'work_place.*' => ['string', 'max:100'],
            'other_benefits' => ['sometimes', 'nullable', 'array', 'max:30'],
            'other_benefits.*' => ['string', 'max:255'],
            'skills' => ['sometimes', 'nullable', 'array', 'max:40'],
            'skills.*' => ['integer', 'exists:skills,id'],
            'gender' => ['sometimes', 'nullable', 'array', 'max:10'],
            'gender.*' => ['string', 'max:100'],
            'trade_course' => ['sometimes', 'nullable', 'string', 'max:255'],
            'professional_certification' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    private function jobData(array $validated): array
    {
        foreach (['job_level', 'employment_status', 'work_place', 'other_benefits', 'skills', 'gender'] as $key) {
            if (array_key_exists($key, $validated)) {
                $validated[$key] = $validated[$key] === null ? null : json_encode($validated[$key]);
            }
        }
        return $validated;
    }

    public function store(Request $request)
    {
        $company = $this->company($request);
        abort_unless($request->user()->can('post-job'), 403);
        $data = $request->validate($this->jobRules(true));

        if ($data['maximum_salary'] < $data['minimum_salary']) {
            return response()->json(['message' => 'Maximum salary must not be below minimum salary.'], 422);
        }

        $job = Job::create($this->jobData($data) + [
            'user_id' => $request->user()->id,
            'company_id' => $company->id,
            'slug' => Str::slug($data['title']) . '-' . Str::lower(Str::random(10)),
            'job_status' => JobStatus::PENDING,
            // Only admins may approve a job for public publication.
            'is_publish' => 0,
        ]);

        return response()->json(['message' => 'Job submitted for approval.', 'job' => $job], 201);
    }

    public function update(Request $request, int $id)
    {
        $job = $this->job($request, $id);
        abort_unless($request->user()->can('post-job'), 403);
        abort_if($job->trashed(), 409, 'Restore the job before editing.');

        $data = $request->validate($this->jobRules(false));
        $min = $data['minimum_salary'] ?? $job->minimum_salary;
        $max = $data['maximum_salary'] ?? $job->maximum_salary;
        if ($max < $min) {
            return response()->json(['message' => 'Maximum salary must not be below minimum salary.'], 422);
        }

        $job->update($this->jobData($data) + [
            'job_status' => JobStatus::PENDING,
            'is_publish' => 0,
        ]);

        return response()->json(['message' => 'Job updated and submitted for approval.', 'job' => $job]);
    }

    public function jobApplicantList(Request $request, int $job_id)
    {
        $job = $this->job($request, $job_id);
        return response()->json(Application::where('job_id', $job->id)
            ->with('user:id,name,email,phone')
            ->latest('id')->paginate(15));
    }

    public function getShortListedApplicant(Request $request, int $job_id)
    {
        $job = $this->job($request, $job_id);
        return response()->json(Application::where('job_id', $job->id)
            ->where('status', 2)->with('user:id,name,email')
            ->latest('id')->paginate(15));
    }

    public function getInterviewedApplicant(Request $request, int $job_id)
    {
        $job = $this->job($request, $job_id);
        return response()->json(Application::where('job_id', $job->id)
            ->where('status', 3)->with('user:id,name,email')
            ->latest('id')->paginate(15));
    }

    public function viewJobseekerProfile(Request $request, int $id)
    {
        $company = $this->company($request);
        $hasApplication = Application::where('user_id', $id)
            ->whereIn('job_id', Job::withTrashed()
                ->where('company_id', $company->id)
                ->where('user_id', $request->user()->id)->select('id'))
            ->exists();
        abort_unless($hasApplication, 404);

        $user = User::findOrFail($id);
        $user->load(['personal_info', 'skills', 'educations', 'trainings', 'work_experiences']);
        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'phone']),
            'profile' => $user->personal_info,
            'skills' => $user->skills,
            'educations' => $user->educations,
            'trainings' => $user->trainings,
            'work_experiences' => $user->work_experiences,
        ]);
    }

    public function getApplicantResume(Request $request, int $user_id, int $application_id)
    {
        $application = Application::where('user_id', $user_id)->findOrFail($application_id);
        $this->job($request, (int) $application->job_id);

        $user = User::with([
            'personal_info', 'work_experiences', 'trainings',
            'skills', 'languages', 'educations',
        ])->findOrFail($user_id);

        return Pdf::loadView('resume.resume', ['user' => $user])->download('applicant-resume.pdf');
    }

    public function updateApplicationStatus(Request $request)
    {
        $data = $request->validate([
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'status' => ['required', 'integer', Rule::in(range(1, 11))],
        ]);

        $application = Application::findOrFail($data['application_id']);
        $this->job($request, (int) $application->job_id);

        $application->update(['status' => $data['status']]);
        return response()->json(['message' => 'Application status updated.', 'application' => $application]);
    }
}
