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
        abort_unless($request->user()->user_type===UserType::JOB_SEEKER,403);
        $countries=getCountries();$user=$request->user()->load(['personal_info','work_experiences','trainings','skills','languages','educations.degreeLevel','educations.degreeName']);
        $user->setRelation('personal_info',$user->personal_info??new PersonalInformation());
        $passport=\Illuminate\Support\Facades\DB::table('applicant_passport_profiles')->where('user_id',$user->id)->first();
        $passportLocked=$passport && (\Illuminate\Support\Facades\DB::table('applications')->where('user_id',$user->id)->exists() || \Illuminate\Support\Facades\DB::table('applicant_private_documents')->where('user_id',$user->id)->where('type','passport')->whereNotNull('locked_at')->exists());
        return response()->view('jobseeker.dashboard.profile',compact('countries','user','passport','passportLocked'))->header('Cache-Control','private, no-store');
    }

    public function updateBasics(Request $request)
    {
        abort_unless($request->user()->user_type===UserType::JOB_SEEKER,403);
        $data=$request->validate(['first_name'=>'required|string|max:100','last_name'=>'nullable|string|max:100','dob'=>'nullable|date|before:today','gender'=>'nullable|in:male,female,other,not_interest','nationality'=>'nullable|string|max:100','phone'=>'nullable|string|max:30','alternate_email'=>'nullable|email|max:255','present_address_line_1'=>'nullable|string|max:255','present_city'=>'nullable|string|max:100','present_postal_code'=>'nullable|string|max:30','present_country'=>'nullable|string|max:100']);
        \Illuminate\Support\Facades\DB::transaction(function()use($request,$data){PersonalInformation::updateOrCreate(['user_id'=>$request->user()->id],$data);$request->user()->update(['name'=>trim($data['first_name'].' '.($data['last_name']??''))]);});
        return redirect()->route('job-seeker-profile')->with('success','Profile updated.');
    }
    public function savePassport(Request $request)
    {
        abort_unless($request->user()->user_type===UserType::JOB_SEEKER,403);
        $data=$request->validate(['passport_number'=>'required|string|regex:/^[A-Za-z0-9 -]{5,30}$/','holder_name'=>'required|string|max:180','issuing_country'=>'required|string|max:100|exists:countries,name','issue_date'=>'nullable|date|before_or_equal:today','expiry_date'=>'required|date|after:today']);
        $number=strtoupper(preg_replace('/[^A-Za-z0-9]/','',$data['passport_number']));
        $hash=hash_hmac('sha256',strtolower(trim($data['issuing_country'])).':'.$number,config('app.key'));
        try {\Illuminate\Support\Facades\DB::transaction(function()use($request,$data,$number,$hash){
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $db=\Illuminate\Support\Facades\DB::class;
            $locked=$db::table('applications')->where('user_id',$request->user()->id)->exists() || $db::table('applicant_private_documents')->where('user_id',$request->user()->id)->where('type','passport')->whereNotNull('locked_at')->exists();
            if($locked && $db::table('applicant_passport_profiles')->where('user_id',$request->user()->id)->exists())throw \Illuminate\Validation\ValidationException::withMessages(['passport_number'=>'Passport details are locked after application submission. Contact support for corrections.']);
            if($db::table('applicant_passport_profiles')->where('number_hash',$hash)->where('user_id','!=',$request->user()->id)->exists())throw \Illuminate\Validation\ValidationException::withMessages(['passport_number'=>'This passport is already associated with another account. Contact support.']);
            $existing=$db::table('applicant_passport_profiles')->where('user_id',$request->user()->id)->first();
            $record=['number_encrypted'=>encrypt($number),'number_hash'=>$hash,'holder_name'=>$data['holder_name'],'issuing_country'=>$data['issuing_country'],'issue_date'=>$data['issue_date']??null,'expiry_date'=>$data['expiry_date'],'updated_at'=>now()];
            if(!$existing)$record['created_at']=now();
            $db::table('applicant_passport_profiles')->updateOrInsert(['user_id'=>$request->user()->id],$record);
        });}catch(\Illuminate\Database\QueryException $e){if(in_array((string)$e->getCode(),['23000','23505'],true))throw \Illuminate\Validation\ValidationException::withMessages(['passport_number'=>'This passport is already associated with an account. Contact support.']);throw $e;}
        return redirect()->route('job-seeker-profile')->with('success','Passport information saved privately.');
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

    public function delete(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user = User::findOrFail(Auth::id());
        $user->delete(); // Existing applications/documents remain for retention.

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Account deleted successfully');
    }
}
