<?php

namespace App\Http\Controllers\API;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\CompanySize;
use App\Models\Country;
use App\Models\Dashboard\Company;
use App\Models\EmployerInformation;
use Illuminate\Http\Request;

class EmployerInformationController extends Controller
{
    private function employer(Request $request): void
    {
        abort_unless($request->user() && $request->user()->user_type === UserType::EMPLOYER,
            403, 'Employer account required.');
    }

    public function getEmployeeInfo(Request $request)
    {
        $this->employer($request);
        return response()->json([
            'user' => $request->user()->only(['id', 'name', 'email', 'phone']),
            'profile' => EmployerInformation::where('user_id', $request->user()->id)->first(),
        ]);
    }

    public function getCountryList(Request $request)
    {
        $this->employer($request);
        return response()->json(Country::where('status', 1)->orderBy('name')->get(['id', 'name']));
    }

    public function updateEmployeeInfo(Request $request)
    {
        $this->employer($request);
        $data = $request->validate([
            'first_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'dob' => ['sometimes', 'nullable', 'date'],
            'gender' => ['sometimes', 'nullable', 'string', 'max:50'],
            'nationality' => ['sometimes', 'nullable', 'string', 'max:100'],
            'passport' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'about_me' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ]);
        $profile = EmployerInformation::updateOrCreate(['user_id' => $request->user()->id], $data);
        return response()->json(['message' => 'Profile saved.', 'profile' => $profile]);
    }

    public function companyInformation(Request $request)
    {
        $this->employer($request);
        return response()->json(Company::where('user_id', $request->user()->id)->first());
    }

    public function companySize(Request $request)
    {
        $this->employer($request);
        return response()->json(CompanySize::orderBy('id')->get());
    }

    public function updateCompanyInfo(Request $request)
    {
        $this->employer($request);
        $existing = Company::where('user_id', $request->user()->id)->first();
        $required = $existing ? 'sometimes' : 'required';

        $data = $request->validate([
            'company_sizes_id' => [$required, 'integer', 'exists:company_sizes,id'],
            'industry_type_id' => [$required, 'integer', 'exists:industry_types,id'],
            'country_id' => [$required, 'integer', 'exists:countries,id'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'estd_year' => ['sometimes', 'nullable', 'string', 'max:20'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'company_address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'business_description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'website_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'contact_per_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'persone_designation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'contact_per_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'contact_per_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
        ]);

        $company = Company::updateOrCreate(['user_id' => $request->user()->id], $data);
        return response()->json(['message' => 'Company saved.', 'company' => $company]);
    }
}
