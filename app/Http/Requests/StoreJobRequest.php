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
    protected function prepareForValidation(): void
    {
        $value=$this->input('benefits_text');
        if($this->has('benefits_text') && $value!==null && !is_string($value))throw \Illuminate\Validation\ValidationException::withMessages(['benefits_text'=>'Benefits must be plain text.']);
        if($this->has('benefits_text'))$this->merge(['other_benefits'=>array_values(array_filter(array_map('trim',preg_split('/\\R/',(string)$this->input('benefits_text')))))]);
    }

    public function rules(): array
    {
        return [
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_logo' => 'nullable|boolean',
            'faq_question' => 'nullable|array|max:12',
            'faq_question.*' => 'nullable|string|max:250',
            'faq_answer' => 'nullable|array|max:12',
            'faq_answer.*' => 'nullable|string|max:2000',
            'title' => 'required|string|max:255',
            'vacancies' => 'required|integer|min:1|max:9999999999',
            'job_category_id' => 'required|integer|exists:job_categories,id',
            'employment_status' => 'required|array', //Frontend title name Job Type
            'employment_status.*' => 'required|exists:job_types,id', //Frontend title name Job Type
            'application_deadline' => 'required|date',
            'resume_receiving_email' => 'required|email|max:255',
            'job_level' => 'required|array',
            'job_level.*' => 'required|string',
            'job_context' => 'nullable|string',
            'job_responsibilities' => 'required|string',
            'work_place' => 'required|array',
            'work_place.*' => 'required|string',
            'country_id' =>'required|integer|exists:countries,id',
            'minimum_salary' => 'required|integer|min:0|max:2147483647',
            'maximum_salary' => 'required|integer|min:0|gte:minimum_salary',
            'additional_salary_info' =>'nullable|string',
            'other_benefits' => 'nullable|array',
            'other_benefits.*' => 'nullable|string',
            'lunch_facilities' =>'nullable|string',
            'salary_review' =>'nullable|string|max:255',
            'festival_bonus' => 'nullable|integer|min:0|max:2147483647',
            'degree_level_id' => 'nullable|integer|min:0|max:2147483647',
            'degree_name_id' => 'nullable|integer|min:0|max:2147483647',
            'trade_course' =>'nullable|string',
            'gender' =>'nullable|array',
            'gender.*' => 'nullable|string',
            'professional_certification' =>'nullable|string',
            'minimum_year' => 'nullable|integer|min:0|max:2147483647',
            'maximum_year' => 'nullable|integer|min:0|max:2147483647',
            'fresher' =>'nullable|string|max:255',
            'skills' => 'required|array',
            'skills.*' => 'required|exists:skills,id',
            'additional_requirements' =>'nullable|string',
            'mini_age' => 'required|integer|between:16,100',
            'max_age' => 'nullable|integer|between:16,100|gte:mini_age',
            'salary_type' => 'required|string|max:50',
        ];
    }
}
