<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h6 class="modal-title" id="exampleModalLabel">{{ __('messages.AdressInformation') }} </h6>
        </div>
        <form action="{{ route('save-personal-information') }}" method="POST" id="form_data">
        @csrf 
        @php 
        $countries = getCountries();
        @endphp
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.AddressType') }} <span class="text-danger">*</span>  </label>
                        <select name="present_address_type" class="form-control" required>
                            <option
                                {{ $user->personal_info && $user->personal_info->permanent_address_type == 'home' ? 'selected' : '' }}
                                value="home">{{ __('messages.Home') }}</option>
                            <option
                                {{ $user->personal_info && $user->personal_info->permanent_address_type == 'work' ? 'selected' : '' }}
                                value="work">{{ __('messages.Work') }}</option>
                            <option
                                {{ $user->personal_info && $user->personal_info->permanent_address_type == 'other' ? 'selected' : '' }}
                                value="other">{{ __('messages.Other') }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Addressline1') }} <span class="text-danger">*</span></label>
                        <input type="text" name="present_address_line_1" class="form-control"
                            value="{{ $user->personal_info ? $user->personal_info->present_address_line_1 : null }}"
                            placeholder="{{ __('messages.Addressline1') }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Postalcode') }}</label>
                        <input type="text" name="present_postal_code" class="form-control"
                            value="{{ $user->personal_info ? $user->personal_info->present_postal_code : null }}"
                            placeholder="{{ __('messages.Postalcode') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.City') }}</label>
                        <input type="text" name="present_city" class="form-control"
                            value="{{ $user->personal_info ? $user->personal_info->present_city : null }}"
                            placeholder="{{ __('messages.City') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Country') }}</label>
                        <select name="present_country" class="form-control">
                            <option value="">{{ __('messages.SelectCountry') }}</option>
                            @foreach($countries as $country)
                                <option
                                    {{ $user->personal_info && $user->personal_info->present_country == $country->name ? 'selected' : '' }}
                                    value="{{ $country->name }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <hr>
            <h6>{{ __('messages.PermanentAddress') }}</h6>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.AddressType') }} <span class="text-danger">*</span></label>
                        <select name="permanent_address_type" class="form-control" required>
                            <option
                                {{ $user->personal_info && $user->personal_info->permanent_address_type == 'home' ? 'selected' : '' }}
                                value="home">{{ __('messages.Home') }}</option>
                            <option
                                {{ $user->personal_info && $user->personal_info->permanent_address_type == 'work' ? 'selected' : '' }}
                                value="work">{{ __('messages.Work') }}</option>
                            <option
                                {{ $user->personal_info && $user->personal_info->permanent_address_type == 'other' ? 'selected' : '' }}
                                value="other">{{ __('messages.Other') }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Addressline1') }} <span class="text-danger">*</span></label>
                        <input type="text" name="permanent_address_line_1" class="form-control"
                            value="{{ $user->personal_info ? $user->personal_info->permanent_address_line_1 : null }}"
                            placeholder="{{ __('messages.Addressline1') }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Postalcode') }}</label>
                        <input type="text" name="permanent_postal_code" class="form-control"
                            value="{{ $user->personal_info ? $user->personal_info->permanent_postal_code : null }}"
                            placeholder="{{ __('messages.Postalcode') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.City') }}</label>
                        <input type="text" name="permanent_city" class="form-control"
                            value="{{ $user->personal_info ? $user->personal_info->permanent_city : null }}"
                            placeholder="{{ __('messages.City') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Country') }}</label>
                        <select name="permanent_country" class="form-control">
                            <option value="">{{ __('messages.SelectCountry') }}</option>
                            @foreach($countries as $country)
                                <option
                                    {{ $user->personal_info && $user->personal_info->permanent_country == $country->name ? 'selected' : '' }}
                                    value="{{ $country->name }}">{{ $country->name }}</option>
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


