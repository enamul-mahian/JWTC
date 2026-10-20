<div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"> {{ __('messages.NewWorkExperience') }}</h5>
        </div>
        <form action="{{ route('save-work-experience-information') }}" method="POST" id="form_data">
        @csrf 
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label"> {{ __('messages.Occupationorpositionheld') }}<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="position_name" placeholder="{{ __('messages.Webdeveloper') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Occupationdescription') }}</label>
                        <textarea name="occupation_description" class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Employer') }}</label>
                        <input type="text" name="employeer" class="form-control" placeholder="{{ __('messages.Employer') }}">
                    </div>
                </div>
            
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.City') }}</label>
                        <input type="text" name="city" class="form-control" placeholder="{{ __('messages.City') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Country') }}</label>
                        <select name="occupation_country" class="form-control">
                            <option value="">{{ __('messages.SelectCountry') }}</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.FromDate') }}</label>
                        <input type="date" name="from_date" class="form-control">
                    </div>
                </div>
            
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label"> {{ __('messages.ToDate') }}</label>
                        <input type="date" name="to_date" class="form-control">
                    </div>
                </div>
            
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="form-check d-flex align-items-center" style="margin-top: 30px">
                            <input type="checkbox" class="form-check-input" id="continuing" name="continuing">
                            <label class="form-check-label" for="continuing">{{ __('messages.continuinghere') }}</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="">{{ __('messages.Mainactivities') }}</label>
                        <textarea name="responsibilities" class="form-control"></textarea>
                        <small>{{ __('messages.characters') }}</small>
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