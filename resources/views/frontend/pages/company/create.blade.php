@extends('frontend.layout.frontMaster')
@section('title', 'Employer Information')
@section('content')
    <section id="latest-jobs" class="section bg-gray mt-4">
        <!-- Page Header Start -->
        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner-header">
                            <h3>{{ __('messages.Manageapplications') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page Header End -->

        <!-- Start Content -->
        <div id="content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-xs-12">
                        @include('frontend.pages.emloyeer.sideMenu')
                    </div>

                    <div class="col-lg-8 col-md-12 col-xs-12">
                        <form action="{{ route('company.information.store') }}" method="post">
                            @csrf
                            <div class="job-alerts-item">
                                <h3 class="alerts-title">{{ __('messages.CompanyInformation') }}</h3>
                                @include('frontend.pages.emloyeer.form')
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
