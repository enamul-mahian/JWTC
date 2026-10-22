<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'vacancies' => 'required|numeric',
            'job_category_id' => 'required|numeric',
            'employment_status' => 'required|array', //Frontend title name Job Type
            'employment_status.*' => 'required|exists:job_types,id', //Frontend title name Job Type
            'application_deadline' => 'required|string|max:255',
            'resume_receiving_email' => 'required|string|max:255',
            'job_level' => 'required|array',
            'job_level.*' => 'required|string',
            'job_context' => 'nullable|string',
            'job_responsibilities' => 'required|string',
            'work_place' => 'required|array',
            'work_place.*' => 'required|string',
            'country_id' =>'required|integer|exists:countries,id',
            'minimum_salary' => 'required|integer|min:0',
            'maximum_salary' => 'required|integer|min:0|gte:minimum_salary',
            'additional_salary_info' =>'nullable|string',
            'other_benefits' => 'nullable|array',
            'other_benefits.*' => 'nullable|string',
            'lunch_facilities' =>'nullable|string',
            'salary_review' =>'nullable|string|max:255',
            'festival_bonus' =>'nullable|numeric',
            'degree_level_id' =>'nullable|numeric',
            'degree_name_id' =>'nullable|numeric',
            'trade_course' =>'nullable|string',
            'gender' =>'nullable|array',
            'gender.*' => 'nullable|string',
            'professional_certification' =>'nullable|string',
            'minimum_year' =>'nullable|numeric',
            'maximum_year' =>'nullable|numeric',
            'fresher' =>'nullable|string|max:255',
            'skills' => 'required|array',
            'skills.*' => 'required|exists:skills,id',
            'additional_requirements' =>'nullable|string',
            'mini_age' => 'required',
            'max_age' => 'nullable',
            'company_id' => 'required',
            'salary_type' => 'required|string|max:50',
        ];
    }
}
