@extends('frontend.layout.frontMaster')
@section('title', __('update profile'))
@push('style')
    <style>
        .preview-img-div {
            height: 110px;
            width: 130px;
            object-fit: cover;
        }
    </style>
@endpush
@section('content')
    <!-- Page Header Start -->
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="inner-header">
                        <h3>{{ __('messages.UpdateYourProfile') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Content section Start -->
    <section id="content" class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-12 col-xs-12">
                    <div class="right-sideabr">
                        @include('frontend.pages.employer.sideMenu')
                    </div>
                </div>
                <div class="col-lg-9 col-md-6 col-xs-12 ">
                    <div class="page-login-form box">
                        <h3>{{ __('messages.UpdateProfile') }}</h3>
                        <form action="{{ route('employer-profile.update', \Auth::user()->id) }}" method="POST"
                            id="form_data" enctype="multipart/form-data">
                            @csrf
                            @method('put')
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label"> {{ __('messages.FirstName') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="first_name"
                                                value="{{ isset($info) ? $info->first_name : '' }}" class="form-control"
                                                placeholder="{{ __('messages.FirstName') }} " required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('messages.LastName') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="last_name"
                                                value="{{ isset($info) ? $info->last_name : '' }}" class="form-control"
                                                placeholder="{{ __('messages.LastName') }} " required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('messages.Dateofbirth') }} <span class="text-danger">*</span></label>
                                            <input type="date" name="dob"
                                                value="{{ isset($info) ? $info->dob : '' }}" class="form-control"
                                                placeholder="{{ __('messages.Dateofbirth') }} " required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('messages.Gender') }}</label>
                                            <select name="gender" class="form-control">
                                                <option {{ isset($info) && $info->gender == 'male' ? 'selected' : '' }}
                                                    value="male">{{ __('messages.Male') }}
                                                </option>
                                                <option {{ isset($info) && $info->gender == 'female' ? 'selected' : '' }}
                                                    value="female">{{ __('messages.Female') }}
                                                </option>
                                                <option {{ isset($info) && $info->gender == 'other' ? 'selected' : '' }}
                                                    value="other">{{ __('messages.Other') }}

                                                </option>
                                                <option
                                                    {{ isset($info) && $info->gender == 'not_interest' ? 'selected' : '' }}
                                                    value="not_interest">{{ __('messages.Donotindicate') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('messages.Passport') }} </label>
                                            <input type="text" class="form-control" name="passport"
                                                value=" {{ isset($info) ? $info->passport : '' }}"
                                                placeholder="messages.Passport">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('messages.Address') }} </label>
                                            <input type="text" class="form-control" value="{{ $info->address ?? '' }}"
                                                name="address" placeholder="{{ __('messages.adrress') }} ">
                                        </div>
                                    </div>
                                    @php
                                        $countries = getCountries();
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('messages.Nationality') }} <span class="text-danger">*</span></label>
                                            <select name="nationality" class="form-control">
                                                <option value="">{{ __('messages.SelectCountry') }}</option>
                                                @foreach ($countries as $country)
                                                    <option
                                                        {{ isset($info) && $info->nationality == $country->name ? 'selected' : '' }}
                                                        value="{{ $country->name }}">{{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('messages.JobCategory') }}</label>
                                            <select name="job_category_id" class="form-control">
                                                <option value="">{{ __('messages.JobCategory') }}</option>
                                                @foreach ($jobCategory as $category)
                                                    <option
                                                        {{ isset($user) && $user->job_category_id == $category->id ? 'selected' : '' }}
                                                        value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-12">
                                        <label for="update-photo">{{ __('messages.Photo') }}</label>
                                        <input type="file" name="photo" class="form-control update-profile-photo">
                                        <div class="row">
                                            <div class="col-lg-6">
                                                <div class="preview-img-div mt-2" style="display: none">
                                                    <img class="preview-img w-100 h-100" alt="">
                                                </div>
                                            </div>
                                            <div class="col-lg-6 mt-2">
                                                <div class="profile-image">
                                                    <img class="img-thumbnail"
                                                        src="{{ asset('assets/images/profilephoto/' . $info?->photo) }}"
                                                        alt="{{ $info?->first_name . $info?->last_name }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-4">
                                        <div class="form-group">
                                            <label class="control-label">{{ __('messages.Aboutme') }}</label>
                                            <textarea name="about_me" id="about" class="form-control">
                                                {{ isset($info) ? $info?->about_me : '' }}
                                            </textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit"
                                    class="btn btn-primary btn-sm">{{ __('messages.Update') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Content section End -->
@endsection

@push('script')
    <script>
        ClassicEditor
            .create(document.querySelector('#about'))
            .catch(error => {
                console.error(error);
            });

        $('.update-profile-photo').on('change', function(e) {
            let photo = e.target.files[0]
            photo = URL.createObjectURL(photo)
            $('.preview-img').attr('src', photo)
            $('.preview-img-div').show()
        })
    </script>
@endpush
