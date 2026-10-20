<?php

namespace App\Http\Requests\Dashboard\Employeer;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeerRequest extends FormRequest
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
            'company_name' => 'required|max:255|string',
            'estd_year' => 'required|max:20|string',
            'company_size' => 'required|max:255|string',
            'country' => 'required|max:255|string',
            'city' => 'required|max:255|string',
            'industry_type' => 'required|max:255|string',
            'company_address' => 'required|min:20|max:255|string',
            'business_description' => 'nullable|max:255|string',
            'trade_license' => 'nullable|max:255|string',
            'website_url' => 'nullable|max:255|string',
            'contact_per_name' => 'required|max:255|string',
            'persone_designation' => 'required|max:255|string',
            'contact_per_email' => 'required|max:255|string',
            'contact_per_phone' => 'required|max:255|string',
        ];
    }
}
