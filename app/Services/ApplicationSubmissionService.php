<?php

namespace App\Services;

use App\Enum\UserType;
use App\Models\Application;
use App\Models\Job;
use App\Models\UniqueCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApplicationSubmissionService
{
    /**
     * Atomically submit one application and consume exactly one approved code.
     * Lock the applicant row so concurrent web/API submissions serialize.
     */
    public function submit(User $applicant, Job $job, float $expectedSalary): Application
    {
        return DB::transaction(function () use ($applicant, $job, $expectedSalary) {
            User::whereKey($applicant->getKey())->lockForUpdate()->firstOrFail();

            if ($applicant->user_type !== UserType::JOB_SEEKER) {
                throw new \DomainException('Only job seekers may apply.');
            }

            $currentJob = Job::whereKey($job->getKey())->firstOrFail();
            if ((int) $currentJob->is_publish !== 1 ||
                ($currentJob->application_deadline && $currentJob->application_deadline < now()->toDateString())) {
                throw new \DomainException('This job is not accepting applications.');
            }

            if (Application::where('user_id', $applicant->getKey())
                ->where('job_id', $currentJob->getKey())->exists()) {
                throw new \DomainException('You have already applied to this job.');
            }

            $code = UniqueCode::where('user_id', $applicant->getKey())
                ->where('is_used', 0)
                ->whereNotNull('code')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$code) {
                throw new \DomainException('Please request and obtain an approved application code.');
            }

            $application = Application::create([
                'user_id' => $applicant->getKey(),
                'employer_id' => $currentJob->company_id,
                'job_id' => $currentJob->getKey(),
                'expected_salary' => $expectedSalary,
                'date_applied' => now()->toDateString(),
                'status' => 1, // First status in the existing 1-11 applicant workflow.
            ]);

            $code->is_used = 1;
            $code->save();

            // After staging's additive migration, include the first status
            // event in the same transaction as code consumption.
            if (\Illuminate\Support\Facades\Schema::hasTable('application_status_events')) {
                $first = app(ApplicationStatusCatalog::class)->find(1);
                DB::table('application_status_events')->insert([
                    'application_id' => $application->id,
                    'actor_id' => $applicant->id,
                    'from_status_id' => null,
                    'to_status_id' => 1,
                    'status_name' => $first['name'] ?? 'Application received',
                    'progress' => $first['progress'] ?? 0,
                    'source' => 'system',
                    'note' => null,
                    'created_at' => now(),
                ]);
            }

            return $application;
        }, 3);
    }
}
