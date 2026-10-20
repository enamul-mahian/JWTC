@extends('dashboard.layout.dasboardMaster')

@section('title', 'General Setting')

@section('content')

<section class="section dashboard">

    <form action="{{ route('setting-update') }}" method="POST" enctype="multipart/form-data">

        @csrf

        <div class="row">

            {{-- Business Information --}}
            <div class="col-lg-6">
                <div class="card">

                    <div class="card-body">

                        <div class="row">
                            <label class="col-sm-6 col-form-label">
                                Business Name
                                <span class="text-danger">*</span>
                            </label>

                            <div class="col-sm-10">
                                <input
                                    type="text"
                                    placeholder="Enter Title"
                                    class="form-control"
                                    name="business_name"
                                    value="{{ old('business_name', $general->business_name ?? '') }}"
                                    required
                                >
                            </div>
                        </div>


                        <div class="row">
                            <label class="col-sm-6 col-form-label">
                                Business Address
                                <span class="text-danger">*</span>
                            </label>

                            <div class="col-sm-10">
                                <input
                                    type="text"
                                    placeholder="Enter Address"
                                    class="form-control"
                                    name="business_address"
                                    value="{{ old('business_address', $general->business_address ?? '') }}"
                                    required
                                >
                            </div>
                        </div>


                        <div class="row">
                            <label class="col-sm-6 col-form-label">
                                Business Phone Number
                                <span class="text-danger">*</span>
                            </label>

                            <div class="col-sm-10">
                                <input
                                    type="tel"
                                    placeholder="Enter Number"
                                    class="form-control"
                                    name="business_number"
                                    value="{{ old('business_number', $general->business_number ?? '') }}"
                                    required
                                >
                            </div>
                        </div>


                        <div class="row">
                            <label class="col-sm-6 col-form-label">
                                Business Email
                                <span class="text-danger">*</span>
                            </label>

                            <div class="col-sm-10">
                                <input
                                    type="email"
                                    placeholder="Enter Email"
                                    class="form-control"
                                    name="business_email"
                                    value="{{ old('business_email', $general->business_email ?? '') }}"
                                    required
                                >
                            </div>
                        </div>


                        <div class="row" style="display:none;">
                            <label class="col-sm-6 col-form-label">
                                Currency Symbol
                            </label>

                            <div class="col-sm-10">
                                <input
                                    type="text"
                                    class="form-control"
                                    name="currency_symbol"
                                    value="{{ old('currency_symbol', $general->currency_symbol ?? '$') }}"
                                >
                            </div>
                        </div>

                    </div>

                </div>
            </div>


            {{-- Logo / Favicon --}}
            <div class="col-lg-6">

                <div class="card">

                    <div class="card-body">

                        <div class="row">

                            <label class="col-sm-4 col-form-label">
                                Logo
                            </label>

                            <div class="col-sm-12">

                                <div class="row">

                                    <div class="col-md-12 mb-3">

                                        <div class="input-group">

                                            <input
                                                type="file"
                                                name="logo"
                                                accept="image/png,image/jpg,image/gif,image/jpeg"
                                                class="form-control"
                                            >

                                        </div>

                                    </div>


                                    <div class="col-md-12 mb-3">

                                        @if ($general && $general->logo)

                                            <img
                                                src="{{ asset('assets/images/setting/' . $general->logo) }}"
                                                alt="Logo"
                                                class="img-fluid"
                                            >

                                        @endif

                                    </div>

                                </div>

                            </div>

                        </div>


                        <div class="row">

                            <label class="col-sm-4 col-form-label">
                                FavIcon
                            </label>

                            <div class="col-sm-12">

                                <div class="row">

                                    <div class="col-md-12 mb-3">

                                        <div class="input-group">

                                            <input
                                                type="file"
                                                name="favicon"
                                                accept="image/png,image/jpg,image/gif,image/jpeg"
                                                class="form-control"
                                            >

                                        </div>

                                    </div>


                                    <div class="col-md-12 mb-3">

                                        @if ($general && $general->favicon)

                                            <img
                                                src="{{ asset('assets/images/setting/' . $general->favicon) }}"
                                                alt="Favicon"
                                                class="img-fluid"
                                            >

                                        @endif

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Meta Settings --}}
            <div class="col-lg-6">

                <div class="card">

                    <div class="card-body">

                        <div class="row">

                            <label class="col-sm-6 col-form-label">
                                Meta Title
                                <span class="text-danger">*</span>
                            </label>

                            <div class="col-sm-10">

                                <input
                                    type="text"
                                    placeholder="Enter meta title"
                                    class="form-control"
                                    name="meta_title"
                                    value="{{ old('meta_title', $general->meta_title ?? '') }}"
                                    required
                                >

                            </div>

                        </div>


                        <div class="row">

                            <label class="col-sm-6 col-form-label">
                                Meta Description
                                <span class="text-danger">*</span>
                            </label>

                            <div class="col-sm-10">

                                <input
                                    type="text"
                                    placeholder="Enter meta description"
                                    class="form-control"
                                    name="meta_description"
                                    value="{{ old('meta_description', $general->meta_description ?? '') }}"
                                    required
                                >

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Office Information --}}
            <div class="col-lg-6">

                <div class="card">

                    <div class="card-body">

                        <div class="row">

                            <label class="col-sm-6 col-form-label">
                                Office Address
                            </label>

                            <div class="col-sm-10">

                                <input
                                    type="text"
                                    placeholder="Enter Address"
                                    class="form-control"
                                    name="address_line"
                                    value="{{ old('address_line', $general->address_line ?? '') }}"
                                >

                            </div>

                        </div>


                        <div class="row">

                            <label class="col-sm-6 col-form-label">
                                Phone
                            </label>

                            <div class="col-sm-10">

                                <input
                                    type="text"
                                    placeholder="Enter Phone"
                                    class="form-control"
                                    name="phone_1"
                                    value="{{ old('phone_1', $general->phone_1 ?? '') }}"
                                >

                            </div>

                        </div>


                        <div class="row">

                            <label class="col-sm-6 col-form-label">
                                Phone 2
                            </label>

                            <div class="col-sm-10">

                                <input
                                    type="text"
                                    placeholder="Enter Phone 2"
                                    class="form-control"
                                    name="phone_2"
                                    value="{{ old('phone_2', $general->phone_2 ?? '') }}"
                                >

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- JOB SEEKER REFERRAL CODE --}}
            <div class="col-lg-6">

                <div class="card">

                    <div class="card-body">

                        <h5 class="card-title">
                            Job Seeker Registration
                        </h5>

                        <div class="row">

                            <label class="col-sm-6 col-form-label">

                                Job Seeker Referral Code

                                <span class="text-danger">*</span>

                            </label>

                            <div class="col-sm-10">

                                <input
                                    type="text"
                                    name="job_seeker_referral_code"
                                    class="form-control"
                                    placeholder="Example: GALLAGHER2026"
                                    value="{{ old('job_seeker_referral_code', $general->job_seeker_referral_code ?? '') }}"
                                    minlength="4"
                                    maxlength="100"
                                    autocomplete="off"
                                    required
                                >

                                <small class="text-muted">

                                    Job seekers must enter this code when creating an account.
                                    You can change this code at any time.

                                </small>


                                @error('job_seeker_referral_code')

                                    <div class="text-danger mt-2">

                                        {{ $message }}

                                    </div>

                                @enderror

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Save Buttons --}}
        <div class="row">

            <div class="col-sm-10">

                <div class="input-group mb-3">

                    <button
                        class="btn btn-info me-2"
                        type="submit"
                    >
                        Save
                    </button>

                    <a
                        class="btn btn-danger"
                        href="{{ route('admin.dashboard') }}"
                    >
                        Cancel
                    </a>

                </div>

            </div>

        </div>

    </form>

</section>

@endsection


@push('custom-script')

<script>

    $(document).ready(function() {

        $('.select2').select2();

    });

</script>

@endpush