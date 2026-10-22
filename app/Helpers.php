<?php

use App\Models\User;
use App\Models\Skill;
use App\Enum\RoleType;
use App\Models\Country;
use App\Models\JobType;
use App\Enum\StatusType;
use App\Lib\FileManager;
use App\Models\Language;
use App\Models\Training;
use App\Constants\Status;
use App\Models\Education;
use App\Models\Application;
use App\Enum\SubscriptionType;
use App\Models\JobseekerSkill;
use App\Models\WorkExperience;
use App\Mail\EmployerReceiveMail;
use App\Models\Dashboard\Company;
use App\Models\ApplicationSetting;
use App\Models\PaymentCredentials;
use App\Models\PersonalInformation;
use App\Jobs\EmployerReceiveMailJob;
use App\Models\UniqueCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Notifications\NewNotification;
use Laravelcm\Subscriptions\Models\Subscription;

function getCountries()
{
    // $jsonUrl = resource_path('views/') . 'countries.json';
    $countries = Country::where('status', StatusType::ACTIVE)->select('name', 'id')->get();
    return $countries;
}

if (!function_exists('industry_type')) {
    function industry_type()
    {
        $industry_types = [
            '1' => 'Advertising Agency',
            '2' => 'Advertising Technology (AdTech) Startup',
            '3' => 'Agro based firms (incl. Agro Processing/Seed/GM)',
            '4' => 'Agro based Startup',
            '5' => 'Airline',
            '6' => 'Amusement Park',
            '7' => 'Animal/Plant Breeding',
            '8' => 'Architecture Firm',
            '9' => 'Artificial Intelligence (AI) Startup',
            '10' => 'Audit Firms /Tax Consultant',
            '11' => 'Automobile',
            '12' => 'Bakery (Cake, Biscuit, Bread)',
            '13' => 'Banks',
            '14' => 'Bar/Pub',
            '15' => 'Battery, Storage cell',
            '16' => 'Beauty Parlor/Saloon/Spa',
            '17' => 'Beverage',
            '18' => 'Bicycle',
            '19' => 'Boutique/ Fashion',
            '20' => 'BPO/ Data Entry Firm',
            '21' => 'Brick',
            '22' => 'Business-to-Business (B2B) Software and Services Startup',
            '23' => 'Buying House',
            '24' => 'Call Center',
            '25' => 'Cellular Phone Operator',
            '26' => 'Cement',
            '27' => 'Cement Industry',
            '28' => 'Catering',
            '29' => 'Cellular Phone Operator',
            '30' => 'Cement',
            '31' => 'Cement Industry',
            '32' => 'Chain shop',
            '33' => 'Chamber',
            '34' => 'Chemical Industries',
            '35' => 'Cinema Hall/Theater',
            '36' => 'Clearing and Forwarding (C and F) Companies',
            '37' => 'Clinic',
            '38' => 'CNG',
            '39' => 'CNG Conversion',
            '40' => 'Coaching Center',
            '41' => 'Club',
            '42' => 'Coal',
            '43' => 'College',
            '44' => 'Coffee Shop',
            '45' => 'Computer Hardware/Network Companies',
            '46' => 'Consulting Firms',
            '47' => 'Convention center',
            '48' => 'Corrugated Tin',
            '49' => 'Cosmetics/Toiletries/Personal Care',
            '50' => 'Credit Rating Agency',
            '51' => 'Crockeries',
            '52' => 'Cultural Centre',
            '53' => 'Dairy',
            '54' => 'Delivery Services Startup',
            '55' => 'Departmental store',
            '56' => 'Design/Printing/Publishing',
            '57' => 'Developer',
            '58' => 'Development Agency',
            '59' => 'Diagnostic Centre',
            '60' => 'Direct Selling/Marketing Service Company',
            '61' => 'Dry cell (Battery)',
            '62' => 'DTP House',
            '63' => 'Dyeing Factor',
            '64' => 'E-Commerce',
            '65' => 'E-Commerce Startup',
            '66' => 'Educational Technology (Edtech) Startup',
            '67' => 'Electric Wire/Cable',
            '68' => 'Electronic Equipment/Home Appliances',
            '69' => 'Embassies/Foreign Consulate',
            '70' => 'Engineering Firms',
            '71' => 'Escalator/Elevator/Lift',
            '72' => 'Event Management',
            '73' => 'F-commerce',
            '74' => 'Farming',
            '75' => 'Fast Food Shop',
            '76' => 'Filling Station',
            '77' => 'Film Production',
            '78' => 'Financial Consultants',
            '79' => 'Financial Technology (Fintech) Startup',
            '80' => 'Fire Fighting and Safety',
            '81' => 'Fisheries',
            '82' => 'Focus Auto Brick and ceramics',
            '83' => 'Food (Packaged)',
            '84' => 'Food (Packaged)/Beverage',
            '85' => 'Fuel/Petroleum',
            '86' => 'Freight forwarding',
            '87' => 'Furniture',
            '88' => 'Furniture Manufacturer',
            '89' => 'Gallery',
            '90' => 'Garments',
            '91' => 'Garments Accessories',
            '92' => 'Gas',
            '93' => 'Golf Club',
            '94' => 'Govt./ Semi Govt./ Autonomous body',
            '95' => 'Grocery shop',
            '96' => 'Group of Companies',
            '97' => 'GSA',
            '98' => 'Handicraft',
            '99' => 'Hatchery',
            '100' => 'Healthcare Startup',
            '101' => 'Healthcare/Lifestyle product',
            '102' => 'Herbal Medicine',
            '103' => 'Hospital',
            '104' => 'Hostel',
            '105' => 'Hotel',
            '106' => 'HVAC System',
            '107' => 'Ice Cream',
            '108' => 'Immigration and Education Consultancy Service',
            '109' => 'Immigration/Visa Processing',
            '110' => 'Importer',
            '111' => 'Individual/Personal Recruitment',
            '112' => 'Industrial Machineries (Generator, Diesel Engine etc.)',
            '1113' => 'Insurance',
            '114' => 'Inventory/Warehouse',
            '115' => 'Investment/Merchant Banking',
            '116' => 'ISP',
            '117' => 'IT Enabled Service',
            '118' => 'Jewelry/Gem',
            '119' => 'Jute Goods/ Jute Yarn',
            '120' => 'Kindergarten',
            '121' => 'Lamps',
            '122' => 'Land Phone',
            '123' => 'Law Firm',
            '124' => 'Leasing',
            '125' => 'Livestock',
            '126' => 'Logistic/Courier/Air Express Companies',
            '127' => 'Logistics Startup',
            '128' => 'LPG Gas/Cylinder Gas',
            '129' => 'Madrasa',
            '130' => 'Manpower Recruitment',
            '131' => 'Manufacturing (FMCG)',
            '132' => 'Manufacturing (Light Engineering and Heavy Industry)',
            '133' => 'Market Research Firms',
            '134' => 'Medical Equipment',
            '135' => 'Medical Equipment',
            '136' => 'Micro-Credit',
            '137' => 'Mineral Water',
            '138' => 'Mining',
            '139' => 'Mobile Accessories',
            '140' => 'Motel',
        ];

        return $industry_types;
    }
}

if (!function_exists('age')) {
    function age()
    {
        $age = 18;

        $values = [];

        for ($i = $age; $i <= 50; $i++) {
            $values[] = $i;
        }

        return $values;
    }
}

function fileUploader($file, $location, $size = null, $old = null, $thumb = null)
{
    $fileManager = new FileManager($file);
    $fileManager->path = $location;
    $fileManager->size = $size;
    $fileManager->old = $old;
    $fileManager->thumb = $thumb;
    $fileManager->upload();
    return $fileManager->filename;
}

function fileManager()
{
    return new FileManager();
}

function getFilePath($key)
{
    return fileManager()->$key()->path;
}

function getFileSize($key)
{
    return fileManager()->$key()->size;
}

function getFileExt($key)
{
    return fileManager()->$key()->extensions;
}

function getThumbnailPath($extension)
{
    switch (strtolower($extension)) {
        case 'pdf':
            return asset('assets/images/placeholder/pdf-icon.png');
        case 'doc':
        case 'docx':
            return asset('assets/images/placeholder/doc-icon.png');
        case 'jpg':
        case 'jpeg':
            return asset('assets/images/placeholder/jpg-icon.png');
        default:
            return asset('assets/images/placeholder/png-icon.png');
    }
}

function sendNotification($userId, $title, $url, $action, $outro)
{
    $user = User::find($userId);
    $notificationData = [
        'intro' => $title,
        'actionText' => $action,
        'actionUrl' => url($url),
        'outro' => $outro,
    ];
    $user->notify(new NewNotification($notificationData));

    return true;
}

function userNotifications()
{
    if (auth()->user()) {
        $user = User::find(auth()->user()->id);
        $unreadNotifications = $user->unreadNotifications;
        return $unreadNotifications;
    } else {
        return [];
    }
}

function badgeData($status)
{
    $html = '';
    if ($status == Status::ENABLE) {
        $html = '<span class="badge rounded-pill bg-info">Enable</span>';
    } elseif ($status == Status::DISABLE) {
        $html = '<span class="badge rounded-pill bg-warning">Disabled</span>';
    }

    return $html;
}

function dateFormat($date)
{
    $formattedDate = $date->format('d F, Y');

    return $formattedDate;
}

function getJobStatus($id)
{
    $userId = auth()->id();

    $application = Application::where('job_id', $id)
        ->where('user_id', $userId)
        ->first();

    return $application ? $application->status : null;
}
function getApplication($id)
{
    $userId = auth()->id();

    $application = Application::where('job_id', $id)
        ->where('user_id', $userId)
        ->first();

    return $application ? $application : null;
}

function getUser()
{
    return Auth::guard('web')->user();
}

function gs()
{
    $setting = ApplicationSetting::latest()->first();
    return $setting;
}

function getSkillName($id)
{
    $skill = Skill::findOrFail($id);
    return $skill->skill_name;
}

if (!function_exists('employee_status')) {
    function employee_status($employmentStatus)
    {
        return JobType::whereIn('id', json_decode($employmentStatus ?? '[]'))->select('name')->get();
    }
}

if (!function_exists('credential')) {
    function credential($credential)
    {
        $data = PaymentCredentials::where('name', $credential)->select('value')->first();

        return $data ? $data->value : null;
    }
}

if (!function_exists('plan_type')) {
    function plan_type()
    {
        $planType = [
            'monthly' => 'Monthly',
            'half_year' => 'Half Year',
            'yearly' => 'Yearly',
        ];

        return $planType;
    }
}

if (!function_exists('active_email')) {
    function active_email($id)
    {
        $active = User::where('id', $id)->select('auto_receive_email')->first();

        return $active->auto_receive_email;
    }
}

if (!function_exists('employer_subscription')) {
    function employer_subscription()
    {
        return 'null';
    }
}

if (!function_exists('company_name')) {
    function company_name($id)
    {
        $company = Company::where('id', $id)->select('company_name')->first();

        return $company->company_name;
    }
}

if (!function_exists('profile_complete')) {
    function profile_complete($user)
    {
        $buyerProfiles = PersonalInformation::where('user_id', Auth::user()->id)->first();
        $workValue = WorkExperience::where('user_id', Auth::user()->id)->first();
        $jobSeekerValue = JobseekerSkill::where('user_id', Auth::user()->id)->first();
        $training = Training::where('user_id', Auth::user()->id)->first();
        $language = Language::where('user_id', Auth::user()->id)->first();
        $education = Education::where('user_id', Auth::user()->id)->first();

        $totalFields = 0;
        $totalWithValue = 0;

        $calculateAndAccumulate = function ($model, $fields) use (&$totalFields, &$totalWithValue) {

            $withValue = 0;
            foreach ($fields as $field) {
                if (isset($model->$field)) {
                    $withValue++;
                }
            }
            $totalFields += count($fields);
            $totalWithValue += $withValue;

        };

        $calculateAndAccumulate($buyerProfiles, [
            'first_name', 'last_name', 'motto', 'about_me', 'dob', 'gender', 'nationality', 'email', 'alternate_email', 'phone', 'alternate_phone', 'instant_messaging', 'website',
            'present_address_type', 'present_address_line_1', 'present_postal_code', 'present_city', 'present_country',
            'permanent_address_type', 'permanent_address_line_1', 'permanent_postal_code', 'permanent_city', 'permanent_country',
        ]);

        $calculateAndAccumulate($workValue, [
            'position_name', 'occupation_description', 'employeer', 'city', 'occupation_country', 'from_date', 'responsibilities',
        ]);

        $calculateAndAccumulate($jobSeekerValue, [
            'skill_name', 'skill_proficiency',
        ]);

        $calculateAndAccumulate($training, [
            'country', 'title', 'topic', 'training_year', 'training_institute', 'training_duration',
        ]);

        $calculateAndAccumulate($language, [
            'language_name', 'listening_proficiency', 'reading_proficiency', 'writting_proficiency',
        ]);

        $calculateAndAccumulate($education, [
            'degree_level_id', 'degree_name_id', 'institute_name', 'result', 'pass_year',
        ]);

        $overallPercentage = ($totalWithValue / $totalFields) * 100;

        return round($overallPercentage);
    }
}

if (!function_exists('employerMessageReceive')) {
    function employerMessageReceive($user_id, $jobCategoryId)
    {
        $jobSeeker = User::where('id', $user_id)->first();
        $users = User::where('job_category_id', $jobCategoryId)->where('user_type', RoleType::EMPLOYER)->get();

        foreach ($users as $user) {
            // dispatch(new EmployerReceiveMailJob($user, $jobSeeker));
            Mail::to($user->email)->send(new EmployerReceiveMail($jobSeeker, $user));
        }
    }
}

if (!function_exists('subscriptionExpireDate')) {
    function subscriptionExpireDate($id)
    {
        $features = Subscription::with('plan')->where('id', $id)->latest()->first();
        $endDate = '';

        if ($features->plan->plan_type == SubscriptionType::MONTHLY) {
            $endDate = $features->updated_at->addMonth();
        } else if ($features->plan->plan_type == SubscriptionType::HALFYEARLY) {
            $endDate = $features->updated_at->addMonths(6);
        } else {
            $endDate = $features->updated_at->addYear();
        }

        return $endDate;
    }
}

if (!function_exists('subscriptionUser')) {
    function subscriptionUser($id)
    {
        $user = User::where('id', $id)->first();

        return $user->name;
    }
}

if (!function_exists("countyWiseJobCount")) {
    function countyWiseJobCount()
    {
        $locationWithCount = Country::withCount('jobs')->get();

        return response()->json($locationWithCount);
    }
}

//Unique codes/................
if (!function_exists("maximumAvailability")) {
    function maximumAvailability()
    {
        $result = 4;

        return $result;
    }
}

if (!function_exists("checkAvailability")) {
    function checkAvailability()
    {
        if (!auth()->check()) {
            return 0;
        }

        // One outstanding request/approved unused code at a time.
        // Used codes remain as history and do not prevent future requests.
        return UniqueCode::where('user_id', auth()->id())
            ->where('is_used', 0)
            ->exists() ? 0 : 1;
    }
}

if (!function_exists("CheckApplyEligibility")) {
    function CheckApplyEligibility()
    {
        if (!auth()->check()) {
            return 0;
        }

        return UniqueCode::where('user_id', auth()->id())
            ->where('is_used', 0)
            ->whereNotNull('code')
            ->exists() ? 1 : 0;
    }
}

if (!function_exists('getJobApplicationStatuses')) {
    function getJobApplicationStatuses()
    {
        return [
            ['id' => 1, 'name' => 'File Under Process of Decision Making', 'progress' => 0],
            ['id' => 2, 'name' => 'File Under Process of Assessment', 'progress' => 20],
            ['id' => 3, 'name' => 'File Under Process of Acceptance', 'progress' => 30],
            ['id' => 4, 'name' => 'File Under Process of Approval', 'progress' => 40],
            ['id' => 5, 'name' => 'Agreement on Board', 'progress' => 50],
            ['id' => 6, 'name' => 'Police/Criminal Clearance Certificate Received', 'progress' => 55],
            ['id' => 7, 'name' => 'Medical Test Report Received', 'progress' => 60],
            ['id' => 8, 'name' => 'COS Letter Processed', 'progress' => 70],
            ['id' => 9, 'name' => 'VFS Appointment Date Approved', 'progress' => 75],
            ['id' => 10, 'name' => 'Visa Processing Complete', 'progress' => 100],
            ['id' => 11, 'name' => 'Rejected / Terminated', 'progress' => 0],
        ];
    }
}

if (!function_exists('getJobApplicationStatus')) {
    function getJobApplicationStatus($id)
    {
        $statuses = [
              ['id' => 1, 'name' => 'File Under Process of Decision Making', 'progress' => 0],
            ['id' => 2, 'name' => 'File Under Process of Assessment', 'progress' => 20],
            ['id' => 3, 'name' => 'File Under Process of Acceptance', 'progress' => 30],
            ['id' => 4, 'name' => 'File Under Process of Approval', 'progress' => 40],
            ['id' => 5, 'name' => 'Agreement on Board', 'progress' => 50],
            ['id' => 6, 'name' => 'Police/Criminal Clearance Certificate Received', 'progress' => 55],
            ['id' => 7, 'name' => 'Medical Test Report Received', 'progress' => 60],
            ['id' => 8, 'name' => 'COS Letter Processed', 'progress' => 70],
            ['id' => 9, 'name' => 'VFS Appointment Date Approved', 'progress' => 75],
            ['id' => 10, 'name' => 'Visa Processing Complete', 'progress' => 100],
            ['id' => 11, 'name' => 'Rejected / Terminated', 'progress' => 0],
        ];

        // Use array filter to find the status by id
        $status = array_filter($statuses, function ($status) use ($id) {
            return $status['id'] === $id;
        });

        // Return the first matched status or null if not found
        return array_values($status)[0] ?? null;
    }
}


