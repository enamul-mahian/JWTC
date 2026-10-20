<div class="row mt-4">
    <div class="col-lg-6">
        {!! Form::label('company_name', __('messages.CompanyName')) !!}
        <label class="text-danger">*</label>
        {!! Form::text('company_name', $employer?->company_name, [
            'class' => 'form-control',
            'placeholder' => __('messages.companyname'),
            'required' => 'required',
        ]) !!}
        @error('company_name')
            <p class="position-absolute text-danger">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>
    <div class="col-lg-6">
        {!! Form::label('estd_year', __('messages.YearofEstablishment')) !!}
        <label class="text-danger">*</label>
        {!! Form::text('estd_year', $employer?->estd_year, [
            'class' => 'form-control',
            'placeholder' => __('messages.establishmentinput'),
            'required' => 'required',
        ]) !!}
        @error('estd_year')
            <p class="position-absolute text-danger">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>
    <div class="col-lg-6 mt-4">
        {!! Form::label('company-size', __('messages.CompanySize')) !!}
        <label class="text-danger">*</label>
        <select name="company_sizes_id" id="" class="form-control" required>
            <option value="">{{ __('messages.selectcompanysize') }}</option>
            @foreach ($companySize as $size)
                <option {{ $employer?->company_sizes?->id == $size->id ? 'selected' : '' }} value="{{ $size->id }}">
                    {{ $size->title }} </option>
            @endforeach
        </select>

        @error('company_size')
            <p class="position-absolute text-danger">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>
    <div class="col-lg-6 mt-4">
        {!! Form::label('country', __('messages.Country')) !!}
        <label class="text-danger">*</label>
        <select class="form-control" name="country_id" id="sel1" required>
            <option>{{ __('messages.SelectCountry') }}</option>
            @foreach (getCountries() as  $value)
                <option value="{{ $value->id }}" {{ optional($employer?->country)->id == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
            @endforeach
        </select>
        @error('country')
            <p class="position-absolute text-danger">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>
    <div class="col-lg-6 mt-4">
        {!! Form::label('city', __('messages.CityName')) !!}
        <label class="text-danger">*</label>
        {!! Form::text('city', $employer?->city, [
            'class' => 'form-control',
            'placeholder' => __('messages.writecityname'),
            'required' => 'required',
        ]) !!}
        @error('city')
            <p class="position-absolute text-danger">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>

    <div class="col-lg-6 mt-4">
        {!! Form::label('industry_type_id', __('messages.IndustryType')) !!}
        <label class="text-danger">*</label>
        <select class="form-control" name="industry_type_id" id="sel1" required>
            <option value="">{{ __('messages.SelectIndustryType') }}</option>
            @foreach ($industryType as $value)
                <option {{ $employer?->industryType?->id == $value?->id ? 'selected' : '' }}
                    value="{{ $value->id }}">
                    {{ $value->name }}</option>
            @endforeach
        </select>
        
        @error('industry_type')
            <p class="position-absolute text-danger">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>

    <div class="col-lg-12 mt-4">
        {!! Form::label('company_address', __('messages.CompanyAddress')) !!}
        <label class="text-danger">*</label>
        {!! Form::text('company_address', $employer?->company_address, [
            'class' => 'form-control',
            'placeholder' => 'messages.writeaddress',
            'required' => 'required',
        ]) !!}
        @error('company_address')
            <p class="position-absolute text-danger">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>

    <div class="col-lg-12 mt-4">
        {!! Form::label('business_description', __('messages.BusinessDescription')) !!}
        {!! Form::textArea('business_description', $employer?->business_description, [
            'class' => 'form-control',
            'placeholder' => 'messages.writebusinessdescription',
            'id' => 'businessDescription',
        ]) !!}
        @error('business_description')
            <p class="position-absolute text-danger">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>

    <div class="col-lg-6 mt-4">
        {!! Form::label('trade_license', __('messages.TradeLicense')) !!}
        {!! Form::text('trade_license', $employer?->trade_license, [
            'class' => 'form-control',
            'placeholder' => __('messages.TradeLicense'),
        ]) !!}
        @error('trade_license')
            <p class="position-absolute text-danger">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>

    <div class="col-lg-6 mt-4">
        {!! Form::label('website_url', __('messages.WebsiteURL')) !!}
        {!! Form::text('website_url', $employer?->website_url, [
            'class' => 'form-control',
            'placeholder' => __('messages.WebsiteURL'),
        ]) !!}
        @error('website_url')
            <p class="position-absolute text-danger">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>
</div>

<hr class="my-4">

<h6 class="mb-4 text-info"> {{ __('messages.ContactInformation') }}</h6>

<div class="row">
    <div class="col-lg-6">
        {!! Form::label('contact_per_name', __('messages.ContactPersonName')) !!}
        <label class="text-danger">*</label>
        {!! Form::text('contact_per_name', $employer?->contact_per_name, [
            'class' => 'form-control',
            'required' => 'required',
        ]) !!}
        @error('contact_per_name')
            <p class="mb-0 position-absolute">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>

    <div class="col-lg-6">
        {!! Form::label('persone_designation', __('messages.ContactPersonDesignation')) !!}
        <label class="text-danger">*</label>
        {!! Form::text('persone_designation', $employer?->persone_designation, [
            'class' => 'form-control',
            'required' => 'required',
        ]) !!}
        @error('persone_designation')
            <p class="mb-0 position-absolute">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>

    <div class="col-lg-6 mt-4">
        {!! Form::label('contact_per_email', __('messages.ContactPersonEmail')) !!}
        <label class="text-danger">*</label>
        {!! Form::text('contact_per_email', $employer?->contact_per_email, [
            'class' => 'form-control',
            'required' => 'required',
        ]) !!}
        @error('contact_per_email')
            <p class="mb-0 position-absolute">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>

    <div class="col-lg-6 mt-4">
        {!! Form::label('contact_per_phone', __('messages.ContactPersonPhone')) !!}
        <label class="text-danger">*</label>
        {!! Form::text('contact_per_phone', $employer?->contact_per_phone, [
            'class' => 'form-control',
            'required' => 'required',
        ]) !!}
        @error('contact_per_phone')
            <p class="mb-0 position-absolute">
                <small><i class="fa-solid fa-triangle-exclamation"></i> {{ $message }}</small>
            </p>
        @enderror
    </div>

    <div class="col-lg-12 mt-4">
        {!! Form::label('photo', __('messages.Photo')) !!}
        {!! Form::file('photo', ['class' => 'form-control company-img']) !!}
        <div class="row mt-4 text-center">
            <div class="col-lg-6">
                <img class="img-thumbnail show-img" style="width: 250px; height:150px; display: none">
            </div>
            <div class="col-lg-6">
                <img class="img-thumbnail" src="{{ asset('assets/images/profilephoto/' . $employer?->photo) }}"
                    alt="{{ $employer?->title }}">
            </div>
        </div>
    </div>
</div>

@push('script')
    <script src="{{ asset('frontend/assets/js/ckeditor.js') }}"></script>

    <script>
        $('.company-img').on('change', function(e) {
            let photo = e.target.files[0]
            photo = URL.createObjectURL(photo)
            $('.show-img').attr('src', photo)
            $('.show-img').show()
        })

        ClassicEditor
            .create(document.querySelector('#companyAddress'))
            .catch(error => {
                console.error(error);
            });
        ClassicEditor
            .create(document.querySelector('#businessDescription'))
            .catch(error => {
                console.error(error);
            });
    </script>
@endpush


