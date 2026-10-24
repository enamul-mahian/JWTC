<?php

namespace App\Http\Controllers\Admin;

use App\Enum\JobStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequest;
use App\Models\Dashboard\Company;
use App\Models\Job;
use App\Services\JobEditorData;
use App\Services\JobPresentation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomJobController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            in_array(strtolower((string) $request->user()?->user_type), ['admin', 'super_admin'], true)
                && $request->user()->can('active-job'),
            403
        );
    }

    public function create(Request $request, JobEditorData $editor)
    {
        $this->authorizeAdmin($request);
        $companies = Company::query()->orderBy('company_name')->get(['id', 'company_name', 'user_id']);
        return view('admin.job.custom-create', $editor->for(new Job(), null) + [
            'companies' => $companies,
            'title' => 'Create Job',
        ]);
    }

    public function store(StoreJobRequest $request)
    {
        $this->authorizeAdmin($request);
        $request->validate(['company_id' => ['required', 'integer', 'exists:companies,id']]);
        $company = Company::findOrFail($request->integer('company_id'));
        abort_unless($company->user_id && \App\Models\User::whereKey($company->user_id)->exists(), 422, 'Company must have an existing owner.');

        $data = JobPresentation::fields($request, $request->validated());
        foreach (['employment_status', 'job_level', 'work_place', 'other_benefits', 'skills', 'gender'] as $field) {
            $data[$field] = json_encode($data[$field] ?? [], JSON_THROW_ON_ERROR);
        }
        unset($data['company_id'], $data['user_id'], $data['slug'], $data['job_status'], $data['is_publish']);
        $data['company_id'] = $company->id;
        $data['user_id'] = $company->user_id;
        $data['slug'] = Str::slug($data['title']) . '-' . Str::lower(Str::random(10));
        $data['job_status'] = JobStatus::PENDING;
        $data['is_publish'] = 0; // Always draft until reviewed.
        $job = Job::create($data);

        return redirect()->route('admin.jobs.edit', $job)->with('success', 'Custom job created as a draft. Review before publishing.');
    }
}
