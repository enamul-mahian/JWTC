@extends('frontend.layout.frontMaster')

@section('title', "$title")

@push('style')
    <style>
        .job_seeker {
            border: 1px solid #0000002e;
            padding: 10px;
            cursor: default;
        }

        .register-active {
            background: #17a2b8;
            color: #fff;
        }

        .text_no_wrap {
            white-space: nowrap;
        }

        /*
        |--------------------------------------------------------------------------
        | Employer registration temporarily hidden
        |--------------------------------------------------------------------------
        */

        .employer {
            display: none !important;
        }

        .epmloyer-register {
            display: none !important;
        }

        .job-seeker-register {
            display: block !important;
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
                        <h3 class="header-inner">
                            Create Job Seeker Account
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->


    <!-- Content Section Start -->
    <section id="content" class="section-padding">

        <div class="container">

            <div class="row justify-content-center text-center">

                <!-- Job Seeker Tab -->
                <div class="col-lg-8 col-md-8 job_seeker register-active">
                    {{ __('messages.JobSeekerRegister') }}
                </div>


                <!--
                ==========================================================
                EMPLOYER TAB TEMPORARILY HIDDEN
                ==========================================================
                -->

                {{--
                <div class="col-lg-4 col-md-4 employer">
                    {{ __('messages.EmployerRegister') }}
                </div>
                --}}


                <div class="col-lg-8 col-md-8 mt-3">


                    <!--
                    ======================================================
                    EMPLOYER FORM TEMPORARILY HIDDEN
                    ======================================================
                    -->

                    {{--
                    <div class="epmloyer-register">

                        <div class="page-login-form box">

                            <h3>
                                {{ __('messages.CreateEmployerAccount') }}
                            </h3>

                            <form
                                class="login-form"
                                action="{{ route('employer-register.store') }}"
                                method="post"
                            >

                                @csrf

                                <input
                                    type="hidden"
                                    name="user_type"
                                    value="{{ App\Enum\UserType::EMPLOYER }}"
                                >


                                <div class="mb-4">
                                    <div class="input-icon">

                                        <i class="lni-user"></i>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="name"
                                            placeholder="{{ __('messages.Name') }}"
                                            value="{{ old('name') }}"
                                        >

                                        @error('name')
                                            <p class="mb-0 position-absolute text-danger">
                                                <small>{{ $message }}</small>
                                            </p>
                                        @enderror

                                    </div>
                                </div>


                                <div class="mb-4">
                                    <div class="input-icon">

                                        <i class="lni-envelope"></i>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="email"
                                            placeholder="{{ __('messages.EmailAddress') }}"
                                            value="{{ old('email') }}"
                                        >

                                        @error('email')
                                            <p class="mb-0 position-absolute text-danger">
                                                <small>{{ $message }}</small>
                                            </p>
                                        @enderror

                                    </div>
                                </div>


                                <div class="mb-4">
                                    <div class="input-icon">

                                        <i class="lni lni-phone"></i>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="phone"
                                            placeholder="{{ __('messages.phonenumber') }}"
                                            value="{{ old('phone') }}"
                                        >

                                        @error('phone')
                                            <p class="mb-0 position-absolute text-danger">
                                                <small>{{ $message }}</small>
                                            </p>
                                        @enderror

                                    </div>
                                </div>


                                <div class="mb-4">
                                    <div class="input-icon">

                                        <i class="lni lni-tab"></i>

                                        <select
                                            name="job_categories_id"
                                            class="form-control"
                                        >

                                            <option value="">
                                                Select Category
                                            </option>

                                            @foreach ($jobCategories as $category)

                                                <option value="{{ $category->id }}">
                                                    {{ $category->name }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>
                                </div>


                                <div class="mb-4">
                                    <div class="input-icon">

                                        <i class="lni-lock"></i>

                                        <input
                                            type="password"
                                            name="password"
                                            class="form-control"
                                            placeholder="{{ __('messages.Password') }}"
                                        >

                                    </div>
                                </div>


                                <div class="mb-4">
                                    <div class="input-icon">

                                        <i class="lni-unlock"></i>

                                        <input
                                            type="password"
                                            name="password_confirmation"
                                            class="form-control"
                                            placeholder="{{ __('messages.RetypePassword') }}"
                                        >

                                    </div>
                                </div>


                                @include('shared.hcaptcha')
                                <button
                                    class="btn btn-common log-btn mt-3"
                                    type="submit"
                                >
                                    {{ __('messages.Register') }}
                                </button>

                            </form>

                        </div>

                    </div>
                    --}}



                    <!--
                    ======================================================
                    JOB SEEKER FORM
                    ======================================================
                    -->

                    <div class="job-seeker-register">

                        <div class="page-login-form box">

                            <h3>
                                {{ __('messages.Jobseekeraccount') }}
                            </h3>


                            <form
                                class="login-form"
                                action="{{ route('employer-register.store') }}"
                                method="post"
                            >

                                @csrf


                                <input
                                    type="hidden"
                                    name="user_type"
                                    value="{{ App\Enum\UserType::JOB_SEEKER }}"
                                >


                                <!-- Name -->
                                <div class="mb-4">

                                    <div class="input-icon">

                                        <i class="lni-user"></i>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="name"
                                            placeholder="{{ __('messages.Name') }}"
                                            value="{{ old('name') }}"
                                        >

                                        @error('name')
                                            <p class="mb-0 position-absolute text-danger">
                                                <small>
                                                    {{ $message }}
                                                </small>
                                            </p>
                                        @enderror

                                    </div>

                                </div>


                                <!-- Email -->
                                <div class="mb-4">

                                    <div class="input-icon">

                                        <i class="lni-envelope"></i>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="email"
                                            placeholder="{{ __('messages.email') }}"
                                            value="{{ old('email') }}"
                                        >

                                        @error('email')
                                            <p class="mb-0 position-absolute text-danger">
                                                <small>
                                                    {{ $message }}
                                                </small>
                                            </p>
                                        @enderror

                                    </div>

                                </div>


                                <!-- Phone -->
                                <div class="mb-4">

                                    <div class="input-icon">

                                        <i class="lni lni-phone"></i>

                                        <input
                                            type="text"
                                            class="form-control"
                                            name="phone"
                                            placeholder="{{ __('messages.phonenumber') }}"
                                            value="{{ old('phone') }}"
                                        >

                                        @error('phone')
                                            <p class="mb-0 position-absolute text-danger">
                                                <small>
                                                    {{ $message }}
                                                </small>
                                            </p>
                                        @enderror

                                    </div>

                                </div>


                                <!-- Category -->
                                <div class="mb-4">

                                    <div class="input-icon">

                                        <i class="lni lni-tab"></i>

                                        <select
                                            name="job_categories_id"
                                            class="form-control"
                                        >

                                            <option value="">
                                                Select Category
                                            </option>

                                            @foreach ($jobCategories as $category)

                                                <option
                                                    value="{{ $category->id }}"
                                                    {{ old('job_categories_id') == $category->id ? 'selected' : '' }}
                                                >
                                                    {{ $category->name }}
                                                </option>

                                            @endforeach

                                        </select>


                                        @error('job_categories_id')
                                            <p class="mb-0 position-absolute text-danger">
                                                <small>
                                                    {{ $message }}
                                                </small>
                                            </p>
                                        @enderror

                                    </div>

                                </div>


                                <!-- Password -->
                                <div class="mb-4">

                                    <div class="input-icon">

                                        <i class="lni-lock"></i>

                                        <input
                                            type="password"
                                            name="password"
                                            class="form-control"
                                            placeholder="{{ __('messages.Password') }}"
                                        >

                                        @error('password')
                                            <p class="mb-0 position-absolute text-danger">
                                                <small>
                                                    {{ $message }}
                                                </small>
                                            </p>
                                        @enderror

                                    </div>

                                </div>


                                <!-- Confirm Password -->
                                <div class="mb-4">

                                    <div class="input-icon">

                                        <i class="lni-unlock"></i>

                                        <input
                                            type="password"
                                            name="password_confirmation"
                                            class="form-control"
                                            placeholder="{{ __('messages.RetypePassword') }}"
                                        >

                                    </div>

                                </div>


                                <!-- Register -->
                                @include('shared.hcaptcha')
                                <button
                                    class="btn btn-common log-btn mt-3"
                                    type="submit"
                                >
                                    {{ __('messages.Register') }}
                                </button>


                                <p class="text-center">

                                    {{ __('messages.Alreadyaccount') }}

                                    <a href="{{ route('login') }}">
                                        {{ __('messages.SignIn') }}
                                    </a>

                                </p>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>
    <!-- Content Section End -->

@endsection