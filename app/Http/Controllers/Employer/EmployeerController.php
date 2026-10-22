<?php

namespace App\Http\Controllers\Employer;

use App\Enum\RoleType;
use App\Enum\StatusType;
use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\Dashboard\JobCategory;
use App\Models\Job;
use App\Models\PersonalInformation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Subscription;
use Session;
use Spatie\Permission\Models\Role;
use Stripe;

class EmployeerController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function index()
    {
        $title = __('Employer Dashboard');
        $jobs = Job::with('company', 'degree_level', 'degree_name', 'country')->withoutTrashed()->where('user_id', Auth::user()->id)->latest()->paginate(3);

        return view('frontend.pages.job.list', compact('jobs', 'title'));
    }

    public function register()
    {
        $title = __('Employer Account Create');

        if (!Auth::user()) {
            $jobCategories = JobCategory::latest()->get();

            return view('frontend.pages.register', compact('title', 'jobCategories'));
        } else {
            return to_route('home');
        }
    }

    public function jobSeekerRegister()
    {
        $title = __('Job Seeker Account Create');

        if (!Auth::user()) {
            return view('frontend.pages.register', compact('title'));
        } else {
            return to_route('home');
        }
    }

    public function registerStore(Request $request)
    {
        // return $request->all();
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'user_type' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['required', 'numeric', 'unique:' . User::class],
            'password' => ['required', 'min:4', 'confirmed'],
            'job_categories_id' => 'required',
            'h-captcha-response' => [
                \Illuminate\Validation\Rule::requiredIf((bool) config('services.hcaptcha.enabled')),
                'nullable', 'string', 'max:8192',
                new \App\Rules\ValidHcaptcha(),
            ],
        ]);

        if ($request->user_type == UserType::EMPLOYER) {
            $role = Role::where('name', RoleType::EMPLOYER)->first();
        } elseif ($request->user_type == UserType::JOB_SEEKER) {
            $role = Role::where('name', RoleType::JOB_SEEKER)->first();
        }

        if (!$role) {
            return to_route('login')->with([
                'message' => 'registration failed.',
                'class' => 'success',
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'user_type' => $request->user_type,
            'email' => $request->email,
            'phone' => $request->phone,
            'job_category_id' => $request->job_categories_id,
            'status' => StatusType::ACTIVE,
            'password' => Hash::make($request->password),
        ]);

        $user->roles()->attach($role->id);

        if ($request->user_type == UserType::JOB_SEEKER) {
            PersonalInformation::create([
                'user_id' => $user->id,
                'first_name' => $request->name,
                'last_name' => $request->name,
            ]);
        }

        Auth::login($user);

        if ($user->user_type == UserType::EMPLOYER) {
            return to_route('employer.dashboard')->with([
                'message' => 'register success',
                'class' => 'success',
            ]);
        } else {
            return to_route('view-jobseeker-profile')->with([
                'message' => 'register success',
                'class' => 'success',
            ]);
        }
    }

    public function changePass()
    {
        $user = User::where('id', Auth::user()->id)->first();

        return view('frontend.pages.employer.passwordChange', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'old_pass' => ['required', 'string'],
            'new_pass' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        if (!$user || !Hash::check($validated['old_pass'], (string) $user->password)) {
            return back()->withErrors(['old_pass' => 'Current password is incorrect.']);
        }

        if (config('security.password_change_otp.enabled')) {
            app(\App\Services\PasswordOtpService::class)->issue(
                $user, Hash::make($validated['new_pass'])
            );
            return back()->with('otp_sent', 'We emailed you a six-digit verification code.');
        }

        $user->forceFill(['password' => Hash::make($validated['new_pass'])])->save();

        return back()->withSuccess('Password updated successfully.');
    }

        public function updateProfilePage()
    {
        $user = Auth::user();

        return view('frontend.pages.employer.update_profile', compact('user'));
    }

    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        return redirect()->back()->withSuccess('profile updated success');
    }

    public function autoReceiveEmail($val)
    {
        User::where('id', Auth::user()->id)->update(['auto_receive_email' => $val]);

        return response()->json($val);
    }

    public function subscriptions()
    {
        return view('frontend.pages.employer.subscription', [
            'plans' => Plan::orderBy('sort_order')->get(),
        ]);
    }


    public function subscription()
    {
        $features = Subscription::with('plan')->where('subscriber_id', Auth::user()->id)->latest()->get();

        return view('frontend.pages.employer.subscription-list', compact('features'));
    }
}
