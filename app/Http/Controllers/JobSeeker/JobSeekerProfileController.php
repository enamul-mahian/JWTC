<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\User;
use App\Enum\RoleType;
use App\Enum\UserType;
use App\Models\UploadedCv;
use App\Models\CoverLetter;
use Illuminate\Http\Request;
use App\Models\EmployerCvView;
use Illuminate\Validation\Rule;
use App\Models\JobseekerUploads;
use App\Mail\EmployerReceiveMail;
use App\Models\PersonalInformation;
use App\Http\Controllers\Controller;
use App\Jobs\EmployerReceiveMailJob;
use App\Models\JobseekerCertificate;
use App\Models\Personal_information;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class JobSeekerProfileController extends Controller
{
    public function profile(Request $request)
    {
        $countries = getCountries();

        return view('jobseeker.dashboard.profile', compact('countries'));
    }

    public function library(Request $request)
    {
        $resumes = UploadedCv::where('user_id', auth()->user()->id)->get();
        $certificates = JobseekerCertificate::where('user_id', auth()->user()->id)->get();
        $uploads = JobseekerUploads::where('user_id', auth()->user()->id)->get();
        $cover = CoverLetter::where('user_id', auth()->user()->id)->get();
        return view('jobseeker.dashboard.library', compact('resumes', 'certificates', 'uploads', 'cover'));
    }

    public function profileData(Request $request)
    {


        $user = User::with(
            ['personal_info', 'work_experiences', 'trainings', 'skills', 'languages', 'educations.degreeLevel', 'educations.degreeName']
        )
        ->where('id', auth()->user()->id)->first();
        // ->where('id', auth()->user()->id)->first();
        $countries = getCountries();


        return view('jobseeker.dashboard.profile_data', compact('user', 'countries'));
    }

    public function profileEdit($id)
    {
        $user = User::with(
            ['personal_info']
        )->where('id', auth()->user()->id)->first();

        return view('jobseeker.form_element.personal_info', compact('user'));
    }

    public function contactEdit($id)
    {
        $user = User::with(
            ['personal_info']
        )->where('id', auth()->user()->id)->first();

        return view('jobseeker.form_element.contact', compact('user'));
    }

    public function addressEdit($id)
    {
        $user = User::with(
            ['personal_info']
        )->where('id', auth()->user()->id)->first();

        return view('jobseeker.form_element.address', compact('user'));
    }

    public function saveProfileInfo(Request $request)
    {

        $inputs = $request->all();
        try {
            $user = auth()->user();
            $inputs['user_id'] = $user->id;

            $name = $request->first_name ? ($request->last_name ? $request->first_name . ' ' . $request->last_name : $request->first_name) : $request->last_name ?? auth()->user()->name;

            $user->name = $name;
            $user->save();

            $matchThese = ['user_id' => $user->id];
            $data = PersonalInformation::updateOrCreate($matchThese, $inputs);

            $profileComplete =  profile_complete($data->user_id);

            if ($profileComplete == 100) {
                employerMessageReceive($data['user_id'], $data->job_category_id );
            }

            return response()->json(['success' => true, 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function resumeViewd()
    {
        $count = EmployerCvView::where('user_id', auth()->user()->id)->count('id');
        return view('jobseeker.dashboard.resume_views', compact('count'));
    }

    public function delete()
    {
        $user = User::withTrashed()->find(Auth::id());
        $user->delete();

        return redirect('/')->with('success', 'Account delete successful');
    }
}
