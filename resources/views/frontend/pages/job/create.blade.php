@extends('frontend.layout.frontMaster')
@section('title', "$title")

@section('content')
    <section id="latest-jobs" class="section bg-gray mt-4">
        <!-- Page Header Start -->
        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner-header">
                            <h3>{{ __('messages.PostAJobs') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page Header End -->

        <!-- Content section Start -->
        <section class="section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-12 col-xs-12">
                        <div class="right-sideabr">
                            @include('frontend.pages.employer.sideMenu')
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-12 col-xs-12">
                        <div class="post-job box">
                            @if ($company == null)
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <p>{{ __('messages.updatedcompanyinformation') }}.</p>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            <h3 class="job-title">{{ __('Post a new Job') }}</h3>
                            {!! Form::open(['route' => 'jobs.store', 'method' => 'post']) !!}
                            @include('frontend.pages.job.form')
                            <button class="btn btn-common" type="submit"> {{ __('messages.Submityourjob') }}</button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Content section End -->
    </section>
@endsection

