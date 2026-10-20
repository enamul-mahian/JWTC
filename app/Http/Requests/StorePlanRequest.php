<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlanRequest extends FormRequest
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
            'name' => 'required',
            'plan_type' => 'required|string|in:monthly,half_year,yearly',
            'description' => 'required',
            'price' => 'required',
            'signup_fee' => 'required',
            'invoice_period' => 'required',
            'trial_period' => 'required',
            'sort_order' => 'required',
            'invoice_interval' => 'required|string|in:month',
            'trial_interval' => 'required|string|in:day',
            'currency' => 'required | max:3',
        ];
    }
}
