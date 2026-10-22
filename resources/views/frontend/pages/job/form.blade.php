<div class="form-group">
    <label class="control-label">{{ __('messages.JobTitle') }}<span class="text-danger"> *</span> </label>
    <input type="text" value="{{ isset($oldValue) ? $oldValue['title'] : $job->title }}" name="title"
        class="form-control" placeholder="{{ __('messages.Writejobtitle') }}" required>

    @error('title')
        <p class="mb-0 position-absolute text-danger">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
        </p>
    @enderror
</div>

<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="form-group">
            <label class="control-label">{{ __('messages.NumberofVacancies') }}<span class="text-danger"> *</span></label>
            <input type="number" value="{{ isset($oldValue) ? $oldValue['vacancies'] : $job->vacancies }}" name="vacancies"
                class="form-control" placeholder="{{ __('messages.entervacancies') }}" min="1" required>
            @error('vacancies')
                <p class="mb-0 position-absolute text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                </p>
            @enderror
        </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="form-group">
            <label class="control-label">Post Date<span class="text-danger"> *</span></label>
            <input type="date" value="{{ isset($oldValue) ? $oldValue['created_at'] : $job->created_at }}" name="created_at"
                class="form-control" placeholder="Enter Date" required>
            @error('created_at')
                <p class="mb-0 position-absolute text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                </p>
            @enderror
        </div>
    </div>
</div>


<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="form-group">
            <label class="control-label">{{ __('messages.JobCategory') }}<span class="text-danger"> *</span></label>
            <select name="job_category_id" class="form-control job_category">
                <option value="">{{ __('messages.Selectjobcategory') }}</option>
                @foreach ($jobCategory as $category)
                    <option
                        {{ $oldValue['job_category_id'] ?? $job->job_category_id == $category->id ? 'selected' : '' }}
                        value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('job_category_id')
                <p class="mb-0 position-absolute text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                </p>
            @enderror
        </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="form-group">
            <label class="control-label">{{ __('messages.Skills') }} <span class="text-danger">*</span> </label>
            <select name="skills[]" id="skills" class="form-control" multiple="multiple" required>
                <option value=""> {{ __('messages.SelectSkills') }} </option>
                <option value=""></option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="form-group">
            <label class="control-label">{{ __('messages.ResumeReceiving') }}<span class="text-danger">
                    *</span></label>

            <input type="email"
                value="{{ isset($oldValue) ? $oldValue['resume_receiving_email'] : (isset($job) ? $job->resume_receiving_email : '') }}"
                name="resume_receiving_email" class="form-control" placeholder="{{ __('messages.exampleemail') }}"
                min="1" required>

            @error('resume_receiving_email')
                <p class="mb-0 position-absolute text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-group">
            <label class="control-label">{{ __('messages.ApplicationDeadline') }}<span class="text-danger">
                    *</span></label>
            <input type="date" name="application_deadline"
                value="{{ isset($oldValue['application_deadline']) ? $oldValue['application_deadline'] : (isset($job) ? $job['application_deadline'] : '') }}"
                class="form-control" placeholder="{{ __('messages.dateFormat') }}" required>

            @error('application_deadline')
                <p class="mb-0 position-absolute text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                </p>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label class="control-label">{{ __('messages.JobType') }}<span class="text-danger"> *</span></label>
            <select name="employment_status[]" id="employment_status" class="form-control" multiple="multiple" required>
                @foreach ($jobTypes as $jobType)
                    <option
                        {{ in_array($jobType->id, json_decode($job['employment_status'] ?? '[]')) ? 'selected' : '' }}
                        value="{{ $jobType->id }}">{{ $jobType->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="form-group">
            <label class="control-label">{{ __('messages.JobLevel') }}<span class="text-danger"> *</span></label>
            <select name="job_level[]" id="job_lavel" class="form-control" multiple="multiple" required>
                <option
                    {{ (isset($oldValue['job_level'][0]) && isset($oldValue['employment_status'][0])) ||
                    (isset($job) &&
                        isset($job['job_level']) &&
                        is_array(json_decode($job['job_level'])) &&
                        in_array('entry', json_decode($job['job_level'])))
                        ? 'selected'
                        : '' }}
                    value="entry"> Entry
                </option>

                <option
                    {{ (isset($oldValue['job_level'][1]) && isset($oldValue['employment_status'][1])) ||
                    (isset($job) &&
                        isset($job['job_level']) &&
                        is_array(json_decode($job['job_level'])) &&
                        in_array('mid', json_decode($job['job_level'])))
                        ? 'selected'
                        : '' }}
                    value="mid"> Mid
                </option>

                <option
                    {{ (isset($oldValue['job_level'][2]) && isset($oldValue['employment_status'][2])) ||
                    (isset($job) &&
                        isset($job['job_level']) &&
                        is_array(json_decode($job['job_level'])) &&
                        in_array('top', json_decode($job['job_level'])))
                        ? 'selected'
                        : '' }}
                    value="top"> Top
                </option>
            </select>
        </div>
    </div>
</div>

<section id="editor">
    <div class="form-group">
        <label for="comment">{{ __('messages.Jobcontext') }}</label>
        <textarea name="job_context" class="form-control" rows="5" id="job_context">
            {{ isset($oldValue['job_context']) ? $oldValue['job_context'] : (isset($job) ? $job['job_context'] : '') }}      
        </textarea>
    </div>
</section>

<section id="editor">
    <div class="form-group">
        <label for="comment">{{ __('messages.JobResponsibilities') }} <span class="text-danger">*</span></label>
        <textarea name="job_responsibilities" class="form-control" rows="5" id="job_responsibilities" required>
            {{ isset($oldValue['job_responsibilities']) ? $oldValue['job_responsibilities'] : (isset($job) ? $job['job_responsibilities'] : '') }}
        </textarea>
    </div>
</section>

<div class="form-group">
    <label class="control-label">{{ __('messages.WorkPlace') }}<span class="text-danger"> *</span></label>
    <div class="row">
        <div class="col-md-3">
            <div class="form-check">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input work_at_office" name="work_place[]"
                        value="work_at_office"
                        {{ (isset($oldValue['work_place'][0]) && $oldValue['work_place'][0] == 'work_at_office') || (isset($job['work_place']) && in_array('work_at_office', json_decode($job['work_place']))) ? 'checked' : '' }}
                        @if (Route::currentRouteName() != 'jobs.edit') required @endif>
                    {{ __('messages.WorkatOffice') }}
                </label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-check">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input work_from_home" name="work_place[]"
                        value="work_from_home"
                        {{ (isset($oldValue['work_place'][1]) && $oldValue['work_place'][1] == 'work_from_home') || (isset($job['work_place']) && in_array('work_from_home', json_decode($job['work_place']))) ? 'checked' : '' }}
                        @if (Route::currentRouteName() != 'jobs.edit') required @endif>
                    {{ __('messages.WorkfromHome') }}
                </label>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="control-label">{{ __('messages.JobLocation') }} <span class="text-danger">*</span> </label>
    <div class="search-category-container">
        <label class="styled-select">
            <select class="dropdown-product selectpicker" name="country_id" required>
                <option value="">{{ __('messages.SelectJobLocation') }}</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}"
                        {{ isset($oldValue) && $oldValue['country_id'] == $location->id ? 'selected' : '' }}
                        {{ isset($job) && $job->country_id == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach

            </select>
            @error('country_id')
                <p class="mb-0 position-absolute text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                </p>
            @enderror
        </label>
    </div>
</div>

<div class="form-group">
    <div class="row">
        <div class="col-md-6">
            <span>{{ __('messages.MinimumSalary') }}<span class="text-danger"> *</span></span>
            <input type="number" min="0" step="1"
                value="{{ old('minimum_salary', $job->minimum_salary ?? '') }}"
                name="minimum_salary" class="form-control" required>
            @error('minimum_salary')
                <p class="mb-0 position-absolute text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                </p>
            @enderror
        </div>
        <div class="col-md-6">
            <span>{{ __('messages.MaximumSalary') }}<span class="text-danger"> *</span></span>
            <input type="number" min="0" step="1"
                value="{{ old('maximum_salary', $job->maximum_salary ?? '') }}"
                name="maximum_salary" class="form-control" required>
            @error('maximum_salary')
                <p class="mb-0 position-absolute text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}
                </p>
            @enderror
        </div>
    </div>
</div>
<section id="editor">
    <div class="form-group">
        <label for="comment">{{ __('messages.AdditionalSalaryInfo') }} </label>
        <textarea name="additional_salary_info" class="form-control" rows="5" id="additional_salary_info">
            {{ isset($oldValue['additional_salary_info']) ? $oldValue['additional_salary_info'] : (isset($job) ? $job['additional_salary_info'] : '') }}
        </textarea>
    </div>
</section>

<section id="editor">
    <div class="form-group">
        <label for="" class="mb-1">
            <h6> {{ __('Compensation & other benefits') }}</h6>
        </label>
        <div class="row">
            <div class="col-md-3">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox"
                            {{ (isset($oldValue['other_benefits']) && in_array('mobile_bill', $oldValue['other_benefits'])) || (isset($job['other_benefits']) && !is_null(json_decode($job['other_benefits'])) && in_array('mobile_bill', json_decode($job['other_benefits']))) ? 'checked' : '' }}
                            class="form-check-input" name="other_benefits[]" value="mobile_bill">
                        {{ __('messages.MobileBill') }}
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="other_benefits[]"
                            {{ (isset($oldValue['other_benefits']) && in_array('pension_policy', $oldValue['other_benefits'])) || (isset($job['other_benefits']) && !is_null(json_decode($job['other_benefits'])) && in_array('pension_policy', json_decode($job['other_benefits']))) ? 'checked' : '' }}
                            value="pension_policy"> {{ __('messages.Pensionpolicy') }}
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="other_benefits[]"
                            {{ (isset($oldValue['other_benefits']) && in_array('tour', $oldValue['other_benefits'])) || (isset($job['other_benefits']) && $job['other_benefits'] != 'null' && in_array('tour', json_decode($job['other_benefits']))) ? 'checked' : '' }}
                            value="tour">
                        {{ __('messages.TourAllowance') }}
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="other_benefits[]"
                            {{ (isset($oldValue['other_benefits']) && in_array('medical', $oldValue['other_benefits'])) || (isset($job['other_benefits']) && $job['other_benefits'] != 'null' && in_array('medical', json_decode($job['other_benefits']))) ? 'checked' : '' }}
                            value="medical">
                        {{ __('messages.MedicalAllowance') }}
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input" name="other_benefits[]"
                            {{ (isset($oldValue['other_benefits']) && in_array('holidays', $oldValue['other_benefits'])) || (isset($job['other_benefits']) && $job['other_benefits'] != 'null' && in_array('holidays', json_decode($job['other_benefits']))) ? 'checked' : '' }}
                            value="holidays">
                        {{ __('messages.Weeklyholidays') }}
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" class="form-check-input"
                            {{ (isset($oldValue['other_benefits'][5]) && in_array('performance_bonus', $oldValue['other_benefits'])) || (isset($job['other_benefits']) && $job['other_benefits'] != 'null' && in_array('performance_bonus', json_decode($job['other_benefits']))) ? 'checked' : '' }}
                            name="other_benefits[]" value="performance_bonus"> {{ __('messages.PerformanceBonus') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="form-group">
            <label class="control-label">{{ __('messages.LunchFacilities') }}</label>
            <div class="search-category-container">
                <label class="styled-select">
                    <select class="dropdown-product selectpicker" name="lunch_facilities">
                        <option value="">{{ __('messages.SelectLunchFacilities') }}</option>
                        <option
                            {{ (isset($oldValue['lunch_facilities']) && $oldValue['lunch_facilities'] == 'partially_subsidize') || (isset($job['lunch_facilities']) && $job['lunch_facilities'] == 'partially_subsidize') ? 'selected' : '' }}
                            value="partially_subsidize">{{ __('messages.PartiallySubsidize') }}
                        </option>

                        <option
                            {{ (isset($oldValue['lunch_facilities']) && $oldValue['lunch_facilities'] == 'full_subsidize') || (isset($job['lunch_facilities']) && $job['lunch_facilities'] == 'full_subsidize') ? 'selected' : '' }}
                            value="full_subsidize">
                            {{ __('messages.FullSubsidize') }}
                        </option>
                    </select>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="form-group">
            <label class="control-label">{{ __('messages.FestivalBonus') }}</label>
            <div class="search-category-container">
                <label class="styled-select">
                    <select class="dropdown-product selectpicker" name="festival_bonus">
                        <option value="">{{ __('messages.SelectFestivalBonus') }}
                        </option>
                        <option
                            {{ (isset($oldValue['festival_bonus']) && $oldValue['festival_bonus'] == 1) || (isset($job['festival_bonus']) && $job['festival_bonus'] == 1) ? 'selected' : '' }}
                            value="1">
                            {{ __('1') }}
                        </option>
                        <option
                            {{ (isset($oldValue['festival_bonus']) && $oldValue['festival_bonus'] == 2) || (isset($job['festival_bonus']) && $job['festival_bonus'] == 2) ? 'selected' : '' }}
                            value="2">
                            {{ __('2') }}
                        </option>
                        <option
                            {{ (isset($oldValue['festival_bonus']) && $oldValue['festival_bonus'] == 3) || (isset($job['festival_bonus']) && $job['festival_bonus'] == 3) ? 'selected' : '' }}
                            value="3">
                            {{ __('3') }}
                        </option>
                        <option
                            {{ (isset($oldValue['festival_bonus']) && $oldValue['festival_bonus'] == 4) || (isset($job['festival_bonus']) && $job['festival_bonus'] == 4) ? 'selected' : '' }}
                            value="4">
                            {{ __('4') }}
                        </option>
                    </select>
                </label>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="form-group">
            <label class="control-label">{{ __('messages.SalaryReview') }}</label>
            <div class="search-category-container">
                <label class="styled-select">
                    <select class="dropdown-product selectpicker" name="salary_review">
                        <option value="">{{ __('messages.SelectSalaryReview') }}</option>

                        <option
                            {{ (isset($oldValue['salary_review']) && $oldValue['salary_review'] == 'half_yearly') || (isset($job) && $job->salary_review == 'half_yearly') ? 'selected' : '' }}
                            value="half_yearly">{{ __('messages.HalfYearly') }}
                        </option>

                        <option
                            {{ (isset($oldValue['salary_review']) && $oldValue['salary_review'] == 'yearly') || (isset($job) && $job->salary_review == 'yearly') ? 'selected' : '' }}
                            value="yearly">
                            {{ __('messages.Yearly') }}
                        </option>
                    </select>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="form-group">
            <label class="control-label">{{ __('messages.SalaryType') }} <span class="text-danger">*</span></label>
            <div class="search-category-container">
                <label class="styled-select">
                    <select class="dropdown-product selectpicker" name="salary_type" required>
                        <option value="">{{ __('messages.SelectSalaryType') }}</option>
                        @foreach (['hourly' => 'Hourly', 'daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $period => $label)
                            <option value="{{ $period }}" @selected(old('salary_type', $job->salary_type ?? '') === $period)>{{ $label }}</option>
                        @endforeach
                        @if (!in_array(old('salary_type', $job->salary_type ?? ''), ['', 'hourly', 'daily', 'weekly', 'monthly', 'yearly'], true))
                            <option value="{{ old('salary_type', $job->salary_type ?? '') }}" selected>Existing salary period (legacy)</option>
                        @endif
                    </select>
                </label>
            </div>
        </div>
    </div>
</div>

<h6 class="mb-1" style="font-size:15px">{{ __('messages.EducationalQualification') }}</h6>
<hr class="text-danger">
<div class="form-group">
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-12 mb-3 mb-md-0 mb-lg-0 ">
            <label class="control-label">{{ __('messages.DegreeLevel') }}</label>
            <select name="degree_level_id" id="degree_level" class="form-control">
                <option value=""> {{ __('messages.SelectDegreeLevel') }} </option>
                @foreach ($degreeLevel as $degree)
                    <option
                        {{ (isset($oldValue['degree_level_id']) && $oldValue['degree_level_id'] == $degree->id) || (isset($job) && $job['degree_level_id'] == $degree->id) ? 'selected' : '' }}
                        value="{{ $degree->id }}">{{ $degree->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 ">
            <label class="control-label">{{ __('messages.DegreeName') }}</label>
            <select name="degree_name_id" id="degree_name" class="form-control">
                <option value=""> {{ __('messages.SelectDegreename') }} </option>
            </select>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="control-label">{{ __('messages.TrainingTradeCourse') }}</label>
    <input type="text" class="form-control" name="trade_course"
        value="{{ isset($oldValue['trade_course']) ? $oldValue['trade_course'] : (isset($job) ? $job->trade_course : '') }}"
        placeholder="{{ __('messages.EnterTrainingTradeCourse') }}">
</div>

<div class="form-group">
    <label class="control-label">{{ __('messages.ProfessionalCertification') }}</label>
    {{-- <input type="text" class="form-control" name="professional_certification"
        placeholder="Enter Professional Certification"
        value="{{ isset($oldValue['professional_certification']) ? $oldValue['professional_certification'] : (isset($job) ? $job->professional_certification : '') }}
        "> --}}

    <input type="text" class="form-control" name="professional_certification"
        placeholder="Enter Professional Certification"
        value="{{ isset($oldValue['professional_certification']) ? $oldValue['professional_certification'] : (isset($job) ? $job->professional_certification : '') }}">
</div>

<div class="form-group">
    <h6 class="control-label">{{ __('messages.Experience') }}</h6>
    <hr class="text-danger">
    <div class="row">
        <div class="col-md-6 col-lg-6 col-sm-12 mb-3 mb-lg-0 mb-md-0">
            <label class="control-label">{{ __('messages.Minimumyearofexperience') }}</label>
            <input type="number" class="form-control" placeholder="Minimum year of experience"
                value="{{ isset($oldValue['minimum_year']) ? $oldValue['minimum_year'] : (isset($job) ? $job->minimum_year : '') }}"
                name="minimum_year" min="0" step="0.01">
        </div>
        <div class="col-md-6 col-lg-6 col-sm-12 mb-3 mb-lg-0 mb-md-0">
            <label class="control-label">{{ __('messages.Maximumyearofexperience') }}</label>
            <input type="number" class="form-control" placeholder="Maximum year of experience"
                value="{{ isset($oldValue['maximum_year']) ? $oldValue['maximum_year'] : '' }}{{ isset($job) ? $job->maximum_year : '' }}"
                name="maximum_year" min="0" step="0.01">
        </div>
        <div class="col-12">
            <div class="form-check mt-2">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input"
                        {{ (isset($oldValue['fresher']) && $oldValue['fresher'] == 'fresher') || (isset($job['fresher']) && $job['fresher'] == 'fresher') ? 'checked' : '' }}
                        name="fresher" value="fresher">
                    {{ __('messages.Freshersarealsoencouragedtoapply') }}
                </label>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="control-label">{{ __('messages.AdditionalRequirements') }}</label>
    <textarea name="additional_requirements" id="additional_requirements" cols="30" rows="5"
        class="form-control">
        {{ isset($oldValue['additional_requirements']) ? $oldValue['additional_requirements'] : (isset($job) ? $job->additional_requirements : '') }}
    </textarea>
</div>

<section>
    <div class="form-group">
        <label for="" class="mb-1">
            <h6> {{ __('messages.Gender') }}</h6>
        </label>
        <div class="row">
            <div class="col-md-2">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" name="gender[]" value="male"
                            {{ (isset($oldValue['gender'][0]) && $oldValue['gender'][0] == 'male') || (isset($job['gender']) && $job['gender'] != 'null' && in_array('male', json_decode($job['gender']))) ? 'checked' : '' }}>
                        {{ __('messages.Male') }}
                    </label>

                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" name="gender[]" value="female"
                            {{ (isset($oldValue['gender'][1]) && $oldValue['gender'][1] == 'female') || (isset($job['gender']) && $job['gender'] != 'null' && in_array('female', json_decode($job['gender']))) ? 'checked' : '' }}>
                        {{ __('messages.Female') }}
                    </label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <label class="form-check-label">
                        <input type="checkbox" name="gender[]" value="other"
                            {{ (isset($oldValue['gender'][2]) && $oldValue['gender'][2] == 'other') || (isset($job['gender']) && $job['gender'] != 'null' && in_array('other', json_decode($job['gender']))) ? 'checked' : '' }}>
                        {{ __('messages.Other') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="form-group">
    <div class="row">
        <div class="col-md-6">
            <label class="control-label">{{ __('messages.MinimumAge') }} <span class="text-danger">* </span></label>
            <select name="mini_age" id="" class="form-control" required>
                <option value=""> {{ __('Any') }} </option>
                @foreach (age() as $age)
                    <option
                        {{ (isset($oldValue['mini_age']) && $oldValue['mini_age'] == $age) || (isset($job) && $job['mini_age'] == $age) ? 'selected' : '' }}
                        value="{{ $age }}"> {{ $age }} </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="control-label">{{ __('messages.MaximumAge') }}</label>
            <select name="max_age" id="" class="form-control">
                <option value=""> {{ __('Any') }} </option>
                @foreach (age() as $age)
                    <option
                        {{ (isset($oldValue['max_age']) && $oldValue['max_age'] == $age) || (isset($job) && $job['max_age'] == $age) ? 'selected' : '' }}
                        value="{{ $age }}"> {{ $age }} </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- @if (Route::currentRouteName() == 'jobs.edit') --}}
<div class="form-group">
    <label class="control-label">{{ __('messages.PublishedStatus') }} <span class="text-danger">* </span></label>
    <select name="is_publish" id="" class="form-control" required>
        <option value=""> {{ __('select status') }} </option>

        <option
            {{ (isset($oldValue['is_publish']) && $oldValue['is_publish'] == 1) || (isset($job) && $job['is_publish'] == 1) ? 'selected' : '' }}
            value="1">
            {{ __('messages.Published') }}
        </option>
        <option
            {{ (isset($oldValue['is_publish']) && $oldValue['is_publish'] == 0) || (isset($job) && $job['is_publish'] == 0) ? 'selected' : '' }}
            value="0">
            {{ __('messages.Unpublished') }}
        </option>
    </select>
</div>
{{-- @endif --}}

{{-- @if (auth::user()->roles() == App\Enum\UserType::ADMIN)
    <div class="form-group">
        <select name="company_id" id="">
            <option value="">select company name</option>
            <option value=""></option>
        </select>
    </div>
@else
    <div class="form-group">
        <label class="control-label">{{ __('Company Name') }}</label>
        <input type="text" class="form-control" value="{{ $company?->company_name }}" readonly>
        <input type="hidden" name="company_id" class="form-control" value="{{ $company?->id }}" readonly>
    </div>
@endif --}}

<div class="form-group">
    <label class="control-label">{{ __('messages.CompanyName') }}</label>
    <input type="text" class="form-control" value="{{ $company?->company_name }}" readonly>
    <input type="hidden" name="company_id" class="form-control" value="{{ $company?->id }}" readonly>
</div>

{{-- <div class="form-group">
    <label class="control-label">{{ __('Company Address') }}</label>
    <textarea name="address" class="form-control" id="" cols="30" rows="5">
        {!! $company->company_address !!}
    </textarea>
</div> --}}


@push('js')
    <script>
        @if (Route::currentRouteName() == 'jobs.edit')
            let degreeName_id = "<?php echo isset($job) ? $job?->degree_name_id : ''; ?>"
        @endif
        const degreeLavel = (id) => {
            axios.get(window.location.origin + "/degree-name/" + id).then(res => {
                let data = res.data;
                data.forEach(value => {
                    $('#degree_name').append(
                        `<option @if (Route::currentRouteName() == 'jobs.edit') ${value.id == degreeName_id ? 'selected' : '' } @endif value="${value.id}"> ${value.name} </option>`
                    )
                });
            })
        }

        $('#degree_level').on('change', function() {
            let id = $(this).val();
            $('#degree_name').empty()
            $('#degree_name').append(`<option  value=""> {{ __('Select Degree name') }} </option>`)
            degreeLavel(id)
        })

        @if (Route::currentRouteName() == 'jobs.edit')
            let degreeLevel_id = "<?php echo isset($job) ? $job?->degree_level_id : ''; ?>"
            degreeLavel(degreeLevel_id)
        @endif

        @if (Route::currentRouteName() == 'jobs.edit')
            let values = {!! isset($job) && $job['skills'] ? json_encode(json_decode($job['skills'])) : '[]' !!};
        @endif

        const jobCategory = (id) => {
            axios.get(window.location.origin + '/get-job-skills/' + id).then(res => {
                let skills = res.data
                skills.forEach(skill => {
                    $('#skills').append(
                        `<option @if (Route::currentRouteName() == 'jobs.edit') ${values.indexOf(skill.id) ? 'selected' : ''} @endif  value="${skill.id}">${skill.skill_name}</option>`
                    );
                });
            })
        }

        $('.job_category').on('change', function() {
            let id = $(this).val()
            $('#skills').empty();
            $('#skills').append(`<option value="">Select Skill </option>`);
            jobCategory(id)
        })

        @if (Route::currentRouteName() == 'jobs.edit')
            let jobCategoryId = "<?php echo isset($job) ? $job?->job_category_id : ''; ?>"
            jobCategory(jobCategoryId)
        @endif

        $('#skills').select2({
            placeholder: 'Select Skills',
            allowClear: true
        })
        $('#employment_status').select2({
            placeholder: 'Select employment status',
            allowClear: true
        })
        $('#job_lavel').select2({
            placeholder: 'Select job level',
            allowClear: true
        })

        $('.work_at_office').on('click', function() {
            let value = $(this).prop('checked') == true ? 1 : 0
            if (value == 1) {
                $('.work_from_home').removeAttr('required')
            } else {
                $('.work_from_home').attr('required', 'required');
            }
        })

        $('.work_from_home').on('click', function() {
            let value = $(this).prop('checked') == true ? 1 : 0
            if (value == 1) {
                $('.work_at_office').removeAttr('required')
            } else {
                $('.work_at_office').attr('required', 'required');
            }
        })

        ClassicEditor
            .create(document.querySelector('#additional_requirements'))
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#job_responsibilities'))
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#job_context'))
            .catch(error => {
                console.error(error);
            });

        ClassicEditor
            .create(document.querySelector('#additional_salary_info'))
            .catch(error => {
                console.error(error);
            });
    </script>
@endpush
