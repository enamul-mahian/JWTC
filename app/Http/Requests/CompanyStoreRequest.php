<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'nullable|numeric',
            'company_name' => 'required|string|max:255',
            'estd_year' => 'required|max:20',
            'company_sizes_id' =>  'required|numeric',
            'country_id' =>   'required|string|max:255',
            'city' =>  'required|string|max:255',
            'industry_type_id' =>  'required|numeric',
            'company_address' => 'required|string|max:255',
            'business_description' => 'nullable|string',
            'trade_license' =>  'nullable|string|max:255',
            'website_url' =>  'nullable|string|max:255',
            'contact_per_name' => 'required|string|max:255',
            'persone_designation' => 'required|string|max:255',
            'contact_per_email' => 'required|string|max:255',
            'contact_per_phone' =>  'required|string|max:255',
        ];
    }
}
