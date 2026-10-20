
<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"> {{ __('messages.ContactInformation') }}</h5>
        </div>
        <form action="{{ route('save-personal-information') }}" method="POST" id="form_data">
            @csrf 
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Email') }}</label>
                        <input type="email" name="email" class="form-control" value="{{ $user->personal_info ? $user->personal_info->email : null }}" placeholder="{{ __('messages.Youremail') }}">
                    </div>
                </div>
            
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.AlternateEmail') }}</label>
                        <input type="email" name="alternate_email" class="form-control" value="{{ $user->personal_info ? $user->personal_info->email : null }}" placeholder="{{ __('messages.Youralternateemail') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Phonenumber') }}</label>
                        <input type="text" name="phone" class="form-control" value="{{ $user->personal_info ? $user->personal_info->phone : null }}" placeholder="{{ __('messages.Yourphonenumber') }}">
                    </div>
                </div>
            
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.AlternatePhonenumber') }}</label>
                        <input type="text" name="alternate_phone" class="form-control" value="{{ $user->personal_info ? $user->personal_info->alternate_phone : null }}"
                            placeholder="{{ __('messages.AlternatePhonenumber') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label"> {{ __('messages.Instantmessaging') }}</label>
                        <input type="text" name="instant_messaging" class="form-control" value="{{ $user->personal_info ? $user->personal_info->instant_messaging : null }}" placeholder="{{ __('messages.Instantmessaging') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Website') }}</label>
                        <input type="text" name="website" class="form-control" value="{{ $user->personal_info ? $user->personal_info->website : null }}" placeholder="{{ __('messages.Website') }}">
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
