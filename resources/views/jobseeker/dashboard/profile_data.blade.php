<div class="form-ad">
    <div class="card">
        <div class="row no-gutters">
          <div class="col-md-8">
            <div class="card-body">
              <h5 class="alerts-title">{{ $user->name }}</h5>
              <p class="card-text">{{ $user->email }}</p>
              <button class="btn-info btn-sm" onclick="document.getElementById('imageInput').click()">{{ __('messages.UploadImage') }}</button>
            </div>
          </div>
          <div class="col-md-4">
            <input type="file" id="imageInput" name="photo" style="display: none;">
            <label for="imageInput" style="margin: 35px">
              @if($user->photo)
              <img src="{{ asset('assets/images/profilephoto/'.$user->photo) }}" class="card-img profile-image" alt="Profile Image">
              @else 
              <img src="https://placekitten.com/100/100" class="card-img profile-image" alt="Profile Image">
              @endif
            </label>
          </div>
        </div>
      </div>

    <div class="divider d-flex justify-content-between align-items-center">
        <h5 class="alerts-title"> {{ __('messages.PerosnalInformation') }}</h5>
        <a href="{{ route('jobseeker-profile-information-data', $user->personal_info->id) }}"
            class="ml-auto btn-info btn-sm btn_modal"> {{ __('messages.PerosnalInformation') }}</a>
    </div>
    @include('jobseeker.partials.personal')

    <div class="divider d-flex justify-content-between align-items-center">
        <h5 class="alerts-title">{{ __('messages.Contact') }}</h5>
        <a href="{{ route('jobseeker-contact-information-data', $user->personal_info->id) }}"
            class="ml-auto btn-info btn-sm btn_modal">{{ __('messages.UpdateContact') }}</a>
    </div>
    @include('jobseeker.partials.contact')

    <div class="divider d-flex justify-content-between align-items-center">
        <h5 class="alerts-title">{{ __('messages.Address') }}</h5>
        <a href="{{ route('jobseeker-address-information-data', $user->personal_info->id) }}"
            class="ml-auto btn-info btn-sm btn_modal">{{ __('messages.UpdateAddress') }}</a>
    </div>
    @include('jobseeker.partials.address')

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
