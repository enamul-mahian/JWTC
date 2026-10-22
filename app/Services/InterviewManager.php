<?php

namespace App\Services;

use App\Enum\UserType;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class InterviewManager
{
    public function ready(): bool
    {
        return Schema::hasTable('application_interviews')
            && Schema::hasTable('application_interview_events');
    }

    public function canManage(User $actor, Application $application): bool
    {
        if (in_array($actor->user_type, [UserType::ADMIN, UserType::SUPER_ADMIN], true)) {
            return $actor->can('manage-interviews');
        }

        if ($actor->user_type !== UserType::EMPLOYER || !$actor->can('post-job')) {
            return false;
        }

        // The employer must own BOTH the job and the associated company.
        return Job::withTrashed()
            ->whereKey($application->job_id)
            ->where('user_id', $actor->id)
            ->whereHas('company', fn ($query) => $query->where('user_id', $actor->id))
            ->exists();
    }

    public function canView(User $actor, Application $application): bool
    {
        return ($actor->user_type === UserType::JOB_SEEKER
            && (int) $application->user_id === (int) $actor->id)
            || $this->canManage($actor, $application);
    }

    public function interview(Application $application): ?object
    {
        abort_unless($this->ready(), 503, 'Interview module is not migrated yet.');

        return DB::table('application_interviews')
            ->where('application_id', $application->id)->first();
    }

    public function events(Application $application)
    {
        abort_unless($this->ready(), 503, 'Interview module is not migrated yet.');

        return DB::table('application_interview_events')
            ->where('application_id', $application->id)
            ->orderBy('created_at')->orderBy('id')
            ->get(['action', 'before_status', 'after_status',
                'before_starts_at_utc', 'after_starts_at_utc', 'note', 'created_at']);
    }

    public function schedule(Application $application, User $actor, array $data, string $action): object
    {
        abort_unless($this->canManage($actor, $application), 403);
        abort_unless($this->ready(), 503);
        if (!in_array($action, ['schedule', 'reschedule'], true)) {
            throw new \InvalidArgumentException('Unsupported scheduling action.');
        }

        // The browser sends local wall time plus a real IANA timezone.
        $local = Carbon::createFromFormat(
            '!Y-m-d\TH:i', $data['starts_at_local'], $data['timezone']
        );
        if (!$local || $local->format('Y-m-d\TH:i') !== $data['starts_at_local']) {
            throw ValidationException::withMessages([
                'starts_at_local' => 'Invalid local appointment time.',
            ]);
        }

        $startsUtc = $local->copy()->utc();
        if ($startsUtc->lte(now('UTC')->addMinutes(15))
            || $startsUtc->gt(now('UTC')->addYear())) {
            throw ValidationException::withMessages([
                'starts_at_local' => 'Select a future time at least 15 minutes away and within one year.',
            ]);
        }

        return DB::transaction(function () use ($application, $actor, $data, $action, $startsUtc) {
            // Serialize first-create and subsequent updates on the parent record.
            Application::whereKey($application->id)->lockForUpdate()->firstOrFail();

            $row = DB::table('application_interviews')
                ->where('application_id', $application->id)->lockForUpdate()->first();

            if ($action === 'schedule' && $row) {
                throw ValidationException::withMessages([
                    'action' => 'An interview exists already. Use reschedule.',
                ]);
            }
            if ($action === 'reschedule' && !$row) {
                throw ValidationException::withMessages([
                    'action' => 'Schedule an interview before rescheduling.',
                ]);
            }
            if ($row && $row->status === 'completed') {
                throw ValidationException::withMessages([
                    'action' => 'A completed interview cannot be changed.',
                ]);
            }

            $link = $data['meeting_url'] ?? $row?->meeting_url;
            if (!$link || !filter_var($link, FILTER_VALIDATE_URL)
                || parse_url($link, PHP_URL_SCHEME) !== 'https'
                || parse_url($link, PHP_URL_USER) !== null
                || parse_url($link, PHP_URL_PASS) !== null) {
                throw ValidationException::withMessages([
                    'meeting_url' => 'A valid secure HTTPS meeting URL is required.',
                ]);
            }

            $now = now('UTC');
            $recordData = [
                'starts_at_utc' => $startsUtc->format('Y-m-d H:i:s'),
                'duration_minutes' => (int) $data['duration_minutes'],
                'timezone' => $data['timezone'],
                'meeting_url' => $link,
                'instructions' => $data['instructions'] ?? null,
                'status' => 'scheduled',
                'updated_by' => $actor->id,
                'updated_at' => $now,
            ];

            if ($row) {
                DB::table('application_interviews')->where('id', $row->id)
                    ->update($recordData);
                $id = $row->id;
            } else {
                $id = DB::table('application_interviews')->insertGetId(
                    $recordData + [
                        'application_id' => $application->id,
                        'organizer_id' => $actor->id,
                        'created_at' => $now,
                    ]
                );
            }

            $this->appendEvent($id, $application->id, $actor->id, $action,
                $row?->status, 'scheduled', $row?->starts_at_utc,
                $startsUtc->format('Y-m-d H:i:s'), $data['note'] ?? null);

            return DB::table('application_interviews')->where('id', $id)->first();
        }, 3);
    }

    public function action(
        Application $application, User $actor, string $action, ?string $reason = null
    ): object {
        abort_unless($this->ready(), 503);
        if (!in_array($action, ['cancel', 'complete', 'confirm', 'request_change'], true)) {
            throw new \InvalidArgumentException('Invalid interview action.');
        }

        $organizer = $this->canManage($actor, $application);
        $applicant = $actor->user_type === UserType::JOB_SEEKER
            && (int) $actor->id === (int) $application->user_id;

        abort_unless(
            ($organizer && in_array($action, ['cancel', 'complete'], true))
            || ($applicant && in_array($action, ['confirm', 'request_change'], true)),
            403
        );

        return DB::transaction(function () use ($application, $actor, $action, $reason) {
            Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $row = DB::table('application_interviews')
                ->where('application_id', $application->id)->lockForUpdate()->first();
            abort_unless($row, 404);

            $transitions = [
                'cancel' => [
                    'scheduled' => 'cancelled', 'confirmed' => 'cancelled',
                    'change_requested' => 'cancelled',
                ],
                'complete' => ['scheduled' => 'completed', 'confirmed' => 'completed'],
                'confirm' => ['scheduled' => 'confirmed'],
                'request_change' => ['scheduled' => 'change_requested', 'confirmed' => 'change_requested'],
            ];
            $newStatus = $transitions[$action][$row->status] ?? null;
            if ($newStatus === null) {
                throw ValidationException::withMessages([
                    'action' => 'This action is not permitted for the current interview status.',
                ]);
            }

            if ($action === 'complete' && Carbon::parse($row->starts_at_utc, 'UTC')->isFuture()) {
                throw ValidationException::withMessages([
                    'action' => 'A future interview cannot be marked completed.',
                ]);
            }
            if (in_array($action, ['confirm', 'request_change'], true)
                && Carbon::parse($row->starts_at_utc, 'UTC')->isPast()) {
                throw ValidationException::withMessages([
                    'action' => 'This appointment time has passed. Contact the organizer.',
                ]);
            }

            DB::table('application_interviews')->where('id', $row->id)->update([
                'status' => $newStatus,
                'updated_by' => $actor->id,
                'updated_at' => now('UTC'),
            ]);

            $this->appendEvent($row->id, $application->id, $actor->id, $action,
                $row->status, $newStatus, $row->starts_at_utc,
                $row->starts_at_utc, $reason);

            return DB::table('application_interviews')->where('id', $row->id)->first();
        }, 3);
    }

    private function appendEvent(
        int $interviewId, int $applicationId, int $actorId, string $action,
        ?string $before, string $after, ?string $beforeTime, ?string $afterTime,
        ?string $note
    ): void {
        DB::table('application_interview_events')->insert([
            'interview_id' => $interviewId,
            'application_id' => $applicationId,
            'actor_id' => $actorId,
            'action' => $action,
            'before_status' => $before,
            'after_status' => $after,
            'before_starts_at_utc' => $beforeTime,
            'after_starts_at_utc' => $afterTime,
            'note' => $note,
            'created_at' => now('UTC'),
        ]);
    }
}
