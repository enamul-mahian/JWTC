@extends('frontend.layout.frontMaster')
@section('title', "$title")

@section('content')
    <section id="latest-jobs" class="section bg-gray mt-4">
        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner-header">
                            <h3>{{ __('messages.UpdateJobs') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-3 col-md-12 col-xs-12">
                        <div class="right-sideabr">
                            @include('frontend.pages.employer.sideMenu')
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-12 col-xs-12">
                        <div class="post-job box">
                            <h3 class="job-title">{{ __('Post a new Job') }}</h3>
                            {!! Form::model($job, ['route' => ['jobs.update', $job], 'method' => 'put']) !!}
                            @include('frontend.pages.job.form')
                            <button class="btn btn-common" type="submit"> {{ __('messages.Updateyourjob') }}</button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
@endsection
