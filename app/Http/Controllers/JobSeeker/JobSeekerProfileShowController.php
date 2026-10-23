<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JobSeekerProfileShowController extends Controller
{
    public function show()
    {
        abort_unless(auth()->user()?->user_type === \App\Enum\UserType::JOB_SEEKER, 403);
        $user = User::with(['personal_info', 'work_experiences', 'trainings', 'skills',
            'languages', 'educations.degreeLevel', 'educations.degreeName'])->findOrFail(auth()->id());
        $passportSummary = null;
        if (Schema::hasTable('applicant_passport_profiles')) {
            $passport = DB::table('applicant_passport_profiles')->where('user_id', $user->id)->first();
            if ($passport) {
                $masked = 'Saved privately';
                try { $masked = '••••••' . substr(decrypt($passport->number_encrypted), -4); }
                catch (\Throwable $error) { report($error); }
                $passportSummary = ['number' => $masked, 'country' => $passport->issuing_country];
            }
        }
        return response()->view('jobseeker.dashboard.profile_show', compact('user', 'passportSummary'))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }
}
