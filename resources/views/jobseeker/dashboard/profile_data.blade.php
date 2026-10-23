<div class="form-ad">
    <div class="divider d-flex justify-content-between align-items-center">
        <h5 class="alerts-title"> {{ __('messages.WorkExperience') }}</h5>
        <a href="{{ route('create-work-experience') }}" class="ml-auto btn-info btn-sm btn_modal">
             {{ __('messages.NewWorkExperience') }}</a>
    </div>
    @include('jobseeker.partials.work_experience')

    <div class="divider d-flex justify-content-between align-items-center">
        <h5 class="alerts-title">{{ __('messages.Skill') }}</h5>
        <a href="{{ route('add-new-skill') }}" class="ml-auto btn-info btn-sm btn_modal">{{ __('messages.AddNewSkill') }}</a>
    </div>
    @include('jobseeker.partials.skill')

    <div class="divider d-flex justify-content-between align-items-center">
        <h5 class="alerts-title">{{ __('messages.Language') }}</h5>
        <a href="{{ route('add-new-language') }}" class="ml-auto btn-info btn-sm btn_modal">
            {{ __('messages.AddNewLanguage') }}</a>
    </div>
    @include('jobseeker.partials.language')


    <div class="divider d-flex justify-content-between align-items-center">
        <h5 class="alerts-title">{{ __('messages.Education') }}</h5>
        <a href="{{ route('add-new-education') }}" class="ml-auto btn-info btn-sm btn_modal">
            {{ __('messages.AddNewEducation') }}</a>
    </div>
    @include('jobseeker.partials.education')


    <div class="divider d-flex justify-content-between align-items-center">
        <h5 class="alerts-title">{{ __('messages.Training') }}</h5>
        <a href="{{ route('add-training') }}" class="ml-auto btn-info btn-sm btn_modal">{{ __('messages.AddNewTraining') }}</a>
    </div>

    @include('jobseeker.partials.training')
</div>
