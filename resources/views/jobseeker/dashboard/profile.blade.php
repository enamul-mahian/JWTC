@extends('frontend.layout.frontMaster')
@section('title', 'Profile')
@Section('content')
    @include('modal.common')
    <style>
        .profile-image {
            max-width: 100%;
            height: auto;
            border-radius: 50%;
        }
    </style>
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="inner-header">
                        <h3>{{ __('messages.UpdateProfile') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section id="content">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-xs-12">
                    <div class="right-sideabr">
                        <h4>{{ __('messages.ManageAccount') }}</h4>
                        @include('jobseeker.partials.sidenav')
                    </div>
                </div>
                <div class="col-lg-8 col-md-8 col-xs-12">
                    <div class="job-alerts-item candidates">
                        <div id="profile-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <script src="{{ asset('frontend/assets/script/job_seeker.js') }}"></script>
@endpush
