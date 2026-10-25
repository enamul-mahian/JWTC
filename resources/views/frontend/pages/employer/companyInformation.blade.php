@extends('frontend.layout.frontMaster')
@section('title', 'Employer Information')
@push('page-style')
    <link rel="stylesheet" href="{{ asset('theme/employer-account.css') }}?v=20261025-1">
@endpush
@section('content')
    <section id="latest-jobs" class="section bg-gray jg-employer-account">
        <!-- Page Header Start -->
        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner-header">
                            <h3>{{ __('messages.Manageapplications') }} <span class="jg-account-breadcrumb">/ {{ __('messages.CompanyInformation') }}</span></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page Header End -->

        <!-- Start Content -->
        <div id="content">
            <div class="container">
                <div class="row jg-employer-account-grid">
                    <aside class="col-lg-4 col-md-12 col-xs-12 jg-account-side" aria-label="Employer account menu">
                        @include('frontend.pages.employer.sideMenu')
                    </aside>

                    <div class="col-lg-8 col-md-12 col-xs-12 jg-account-main">
                        <form action="{{ route('company.information.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="job-alerts-item">
                                <h3 class="alerts-title">{{ __('CompanyInformation') }}</h3>
                                @include('frontend.pages.employer.form')
                                <button class="btn btn-common mt-4">{{ __('messages.submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Content -->
    </section>
@endsection

@if (Session::has('message'))
    @push('js')
        <script>
            Swal.fire({
                position: "top-end",
                icon: "<?php echo session('class')?>",
                title:  "<?php echo session('message')?>",
                showConfirmButton: false,
                timer: 2000,
                toast : true
            });
        </script>
    @endpush
@endif


