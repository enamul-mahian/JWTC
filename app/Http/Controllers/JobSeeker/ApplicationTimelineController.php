<?php

namespace App\Http\Controllers\JobSeeker;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ApplicationStatusCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApplicationTimelineController extends Controller
{
    public function show(Request $request, int $application, ApplicationStatusCatalog $catalog)
    {
        abort_unless($request->user()?->user_type === UserType::JOB_SEEKER, 403);

        // Never retrieve by ID alone; ownership must apply before loading events.
        $record = Application::with(['job.company'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($application);

        $events = Schema::hasTable('application_status_events')
            ? DB::table('application_status_events')
                ->where('application_id', $record->id)
                ->orderBy('created_at')->orderBy('id')->get()
            : collect();

        return view('jobseeker.application.timeline', [
            'application' => $record,
            'statuses' => $catalog->all(),
            'current' => $catalog->find((int) $record->status),
            'events' => $events,
        ]);
    }
}
