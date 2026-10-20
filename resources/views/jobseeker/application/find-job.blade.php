@extends('frontend.layout.frontMaster')
@Section('title', 'Jobs')
    @Section('content')

        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner-header">
                            <h3>{{ __('messages.Jobs') }}</h3>
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
                            <h4>{{ __('messages.Jobs') }}</h4>
                            @include('jobseeker.partials.sidenav')
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-8 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <form action="{{ route('job-search') }}" method="POST" id="job_search">
                                    <div class="wrap-search-filter row">
                                        <div class="col-lg-5 col-md-5 col-xs-12">
                                            <input type="text" class="form-control" name="title" placeholder="{{ __('messages.KeywordNameTag') }}">
                                        </div>
                                        <div class="col-lg-5 col-md-5 col-xs-12">
                                            <select name="location" class="form-control" id="">
                                                <option value="">{{ __('messages.SelectLocation') }}</option>
                                                @foreach ($locations as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
        
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-xs-12">
                                            <button type="submit" class="btn btn-common float-right">{{ __('messages.Filter') }}</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="row mt-3" id="jobs_table"> </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endsection

        @push('script')
            <script src="{{ asset('frontend/assets/script/application.js') }}"></script>
        @endpush
