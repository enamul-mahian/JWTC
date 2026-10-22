@extends('frontend.layout.frontMaster')
@Section('title', 'Jobseeker Dashbaord')
@Section('content')
    @if(config('jwtc_design.enabled'))
        <section class="jw-workspace-intro container" aria-labelledby="jw-applicant-title">
            <p class="jw-eyebrow">Applicant workspace</p>
            <h1 id="jw-applicant-title">Your career dashboard</h1>
            <p>Manage your profile, applications and official recruitment updates from one account.</p>
            <nav class="jw-workspace-actions" aria-label="Applicant actions">
                <a href="{{ route('search-jobs') }}">Find Jobs</a>
                <a href="{{ route('applied-jobs') }}">My Applications</a>
                <a href="{{ route('interviews.applicant.index') }}">Interviews</a>
                @if(config('application_chat.enabled'))
                    <a href="{{ route('application-chat.index') }}">Private Messages</a>
                @endif
                @if(config('support_center.enabled'))
                    <a href="{{ route('support.index') }}">Support</a>
                @endif
            </nav>
        </section>
    @endif
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
                        <h3>{{ __('messages.ManageProfile') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div id="content">
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
    </div>
    @endsection

    @push('script')
        <script src="{{ asset('frontend/assets/script/job_seeker.js') }}"></script>
    @endpush
