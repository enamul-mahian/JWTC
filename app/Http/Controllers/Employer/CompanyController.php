<?php

namespace App\Http\Controllers\Employer;

use App\Enum\StatusType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyStoreRequest;
use App\Models\CompanySize;
use App\Models\Dashboard\Company;
use App\Models\IndustryType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:employer-list'])->only('index');
    }

    /**
     * Display the company in admin panel.
     */
    public function index()
    {
        $employers = Company::with('company_sizes', 'industryType')->latest()->get();
        $user = User::all();
        return view('admin.employer.index', compact('employers', 'user'));
    }

    /**
     * Display the specified resource.
     */
    public function companyInformation()
    {
        $user = User::where('id', Auth::user()->id)->first();
        $employer = Company::with('company_sizes', 'country', 'industryType')->where('user_id', $user->id)->first();
        $companySize = CompanySize::where('status', StatusType::ACTIVE)->select('id', 'title')->get();
        $industryType = IndustryType::where('status', StatusType::ACTIVE)->get();

        return view('frontend.pages.employer.companyInformation', compact('employer', 'user', 'companySize', 'industryType'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanyStoreRequest $request)
    {
        // Validate the request inputs using inline validation rules
        $inputs = $request->validate([
            'company_name' => 'required|string|max:255',
            'estd_year' => 'required|integer|digits:4',
            'company_sizes_id' => 'required|exists:company_sizes,id',
            'city' => 'required|string|max:255',
            'industry_type_id' => 'required|exists:industry_types,id',
            'company_address' => 'required|string|max:255',
            'business_description' => 'nullable|string',
            'trade_license' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'contact_per_name' => 'required|string|max:255',
            'persone_designation' => 'nullable|string|max:255',
            'contact_per_email' => 'required|email|max:255',
            'contact_per_phone' => 'required|string|max:15',
            'country_id' => 'required|exists:countries,id', // Assuming you have a countries table
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust max size as necessary
        ]);

        $uid = auth()->user()->id;
        $company = Company::where('user_id', $uid)->first();

        $inputs['user_id'] = $uid;
        $inputs['country'] = $request->country_id;

        if ($request->hasFile('photo')) {
            $old = $company->photo ?? null;
            $image = $request->file('photo');
            $photo = fileUploader($image, getFilePath('profilephoto'), getFileSize('profilephoto'), $old);
            $inputs['photo'] = $photo;
        }

        // dd($inputs);

        $matchThese = ['user_id' => $uid];
        Company::updateOrCreate($matchThese, $inputs);

        return to_route('company.information')->with([
            'message' => 'information update success',
            'class' => 'success',
        ]);
    }

    public function update(Request $request)
    {
        if ($request->mode == 0) {
            DB::table('companies')->where('id', $request->id)->update(['status' => $request->mode]);
        } else {
            DB::table('companies')->where('id', $request->id)->update(['status' => $request->mode]);
        }

        return response()->json(['status', $request->mode]);
    }
}
