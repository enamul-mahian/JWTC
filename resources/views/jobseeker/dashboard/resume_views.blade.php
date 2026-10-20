@extends('frontend.layout.frontMaster')
@Section('title', 'Resume Views')
@Section('content')

<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="inner-header">
                    <h3>{{ __('messages.ResumeViews') }}</h3>
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
                    <h4>{{ __('messages.ResumeViews') }}</h4>
                    @include('jobseeker.partials.sidenav')
                </div>
            </div>
            <div class="col-lg-8 col-md-8 col-xs-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            {{ $count }} {{ __('messages.EmployersViewedProfile') }}
                        </h5>
                        <p class="card-text">
                            {{ __('messages.viewedyourprofile') }}
                        </p>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</div>
@endsection


