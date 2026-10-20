
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="exampleModalLabel">{{ __('messages.PersonalInfo') }} </h3>
        </div>
        <form action="{{ route('save-personal-information') }}" method="POST" id="form_data">
            @csrf 
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Firstname') }} <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" value="{{ $user->personal_info->first_name? $user->personal_info->first_name : null }}" class="form-control" placeholder="{{ __('messages.Enterfirstname') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Lastnames') }} <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" value="{{ $user->personal_info? $user->personal_info->last_name : null }}" class="form-control" placeholder="{{ __('messages.EnterLastnames') }}" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Mottoinlife') }} </label>
                            <textarea name="motto" class="form-control">{{ $user->personal_info ? $user->personal_info->motto: null  }} </textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Aboutme') }}</label>
                            <textarea name="about_me"  class="form-control">{{ $user->personal_info ? $user->personal_info->about_me : null }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Dateofbirth') }}</label>
                            <input type="date" name="dob" value="{{ $user->personal_info ? $user->personal_info->dob : null }}" class="form-control" placeholder="{{ __('messages.Dateofbirth') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Gender') }}</label>
                            <select name="gender" class="form-control">
                                <option value="male" {{ $user->personal_info && $user->personal_info->gender == 'male' ? 'selected' : '' }}>{{ __('messages.Male') }}</option>
                                <option value="female" {{ $user->personal_info && $user->personal_info->gender == 'female' ? 'selected' : '' }}>{{ __('messages.Female') }}</option>
                                <option value="other" {{ $user->personal_info && $user->personal_info->gender == 'other' ? 'selected' : '' }}>{{ __('messages.Other') }}</option>
                                <option value="not_interest" {{ $user->personal_info && $user->personal_info->gender == 'not_interest' ? 'selected' : '' }}>{{ __('messages.Donotindicate') }}</option>
                            </select>
                            
                        </div>
                    </div>
                    @php
                        $countries = getCountries();
                    @endphp
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Nationality') }}</label>
                            <select name="nationality" class="form-control">
                                <option value="">{{ __('messages.SelectCountry') }}</option>
                                @foreach($countries as $country)
                                    <option {{ $user->personal_info && $user->personal_info->nationality == $country->name ? 'selected' : '' }} value="{{ $country->name }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">{{ __('messages.Close') }}</button>
                <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.Save') }}</button>
            </div>
        </form>
    </div>
</div>


