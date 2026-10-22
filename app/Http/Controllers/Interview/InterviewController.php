<?php

namespace App\Http\Controllers\Interview;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use App\Services\InterviewManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InterviewController extends Controller
{
    private function isApi(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    private function application(int $id): Application
    {
        return Application::with(['job', 'user:id,name,email'])
            ->findOrFail($id);
    }

    public function applicantIndex(Request $request, InterviewManager $interviews)
    {
        abort_unless($request->user()?->user_type === UserType::JOB_SEEKER, 403);
        abort_unless($interviews->ready(), 503);

        $items = Application::with('job')
            ->where('user_id', $request->user()->id)
            ->whereIn('id', DB::table('application_interviews')->select('application_id'))
            ->latest('id')->paginate(15);

        if ($this->isApi($request)) {
            return response()->json($items);
        }
        return view('interviews.applicant_index', ['applications' => $items]);
    }

    public function applicantShow(Request $request, int $application, InterviewManager $interviews)
    {
        abort_unless($request->user()?->user_type === UserType::JOB_SEEKER, 403);

        $record = Application::with('job')
            ->where('user_id', $request->user()->id)
            ->findOrFail($application);
        $interview = $interviews->interview($record);
        abort_unless($interview, 404);

        $data = [
            'application' => $record,
            'interview' => $interview,
            'events' => $interviews->events($record),
        ];
        if ($this->isApi($request)) {
            return response()->json($data);
        }
        return view('interviews.applicant_show', $data);
    }

    public function managerIndex(Request $request, InterviewManager $interviews)
    {
        $actor = $request->user();
        abort_unless($actor, 401);
        abort_unless($interviews->ready(), 503);

        if (in_array($actor->user_type, [UserType::ADMIN, UserType::SUPER_ADMIN], true)) {
            abort_unless($actor->can('manage-interviews'), 403);
            $query = Application::query();
        } elseif ($actor->user_type === UserType::EMPLOYER && $actor->can('post-job')) {
            $query = Application::whereHas('job', function ($q) use ($actor) {
                $q->where('user_id', $actor->id)
                    ->whereHas('company', fn ($company) => $company->where('user_id', $actor->id));
            });
        } else {
            abort(403);
        }

        $applications = $query->with(['job', 'user:id,name,email'])
            ->latest('id')->paginate(15);

        if ($this->isApi($request)) {
            return response()->json($applications);
        }
        return view('interviews.manager_index', compact('applications'));
    }

    public function managerShow(Request $request, int $application, InterviewManager $interviews)
    {
        $record = $this->application($application);
        abort_unless($request->user() && $interviews->canManage($request->user(), $record), 403);
        $current = $interviews->interview($record);

        $data = [
            'application' => $record,
            'interview' => $current,
            'events' => $interviews->events($record),
        ];

        if ($this->isApi($request)) {
            return response()->json($data);
        }
        return view('interviews.manager_show', $data);
    }

    public function schedule(Request $request, int $application, InterviewManager $interviews)
    {
        $record = $this->application($application);
        abort_unless($request->user() && $interviews->canManage($request->user(), $record), 403);

        $data = $request->validate([
            'action' => ['required', Rule::in(['schedule', 'reschedule'])],
            'starts_at_local' => ['required', 'date_format:Y-m-d\TH:i'],
            'timezone' => ['required', 'timezone', 'max:64'],
            'duration_minutes' => ['required', 'integer', Rule::in([15, 30, 45, 60, 90, 120])],
            'meeting_url' => ['nullable', 'url', 'max:2000', 'regex:/^https:\/\//i'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $current = $interviews->schedule(
            $record, $request->user(), $data, $data['action']
        );

        if ($this->isApi($request)) {
            return response()->json(['message' => 'Interview scheduled and audited.', 'interview' => $current],
                $data['action'] === 'schedule' ? 201 : 200);
        }

        return redirect()->route('interviews.manage.show', $application)
            ->with('success', 'Interview schedule saved.');
    }

    public function act(Request $request, int $application, InterviewManager $interviews)
    {
        $record = $this->application($application);
        $data = $request->validate([
            'action' => ['required', Rule::in(['cancel', 'complete', 'confirm', 'request_change'])],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $current = $interviews->action(
            $record, $request->user(), $data['action'], $data['reason'] ?? null
        );

        if ($this->isApi($request)) {
            return response()->json(['message' => 'Interview response recorded.', 'interview' => $current]);
        }
        return redirect()->route(
            $request->user()->user_type === UserType::JOB_SEEKER
                ? 'interviews.applicant.show' : 'interviews.manage.show',
            $application
        )->with('success', 'Interview response recorded.');
    }
}
