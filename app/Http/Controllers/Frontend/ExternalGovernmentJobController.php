<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExternalGovernmentJobController extends Controller
{
    private function visible()
    {
        return DB::table('external_job_imports')
            ->where('status', 'published')
            ->where('application_deadline', '>=', today()->toDateString())
            ->where('posted_on', '>=', today()->subDays(14)->toDateString())
            ->where('source', 'uk_government_jobs');
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable','string','max:120'],
            'page' => ['nullable','integer','min:1'],
        ]);
        $jobs = $this->visible();
        if (trim($filters['q'] ?? '') !== '') {
            $term = '%'.str_replace(['%', '_'], ['\%', '\_'], trim($filters['q'])).'%';
            $jobs->where(function ($q) use ($term) {
                $q->where('title','like',$term)->orWhere('company_name','like',$term)
                    ->orWhere('location','like',$term);
            });
        }
        return response()->view('frontend.pages.government-jobs', [
            'jobs' => $jobs->orderByDesc('posted_on')->orderByDesc('id')->paginate(12)->withQueryString(),
        ])->header('Cache-Control', 'public, max-age=120');
    }

    public function show(int $id)
    {
        $job = $this->visible()->where('id', $id)->first();
        abort_unless($job, 404);
        return response()->view('frontend.pages.government-job', compact('job'))
            ->header('Cache-Control', 'public, max-age=120');
    }
}
