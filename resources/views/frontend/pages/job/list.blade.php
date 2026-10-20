@extends('frontend.layout.frontMaster')
@section('title', "$title")
@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('content')
    <section id="latest-jobs" class="section bg-gray mt-4">
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
        <div id="content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-xs-12">
                        <div class="right-sideabr">
                            @include('frontend.pages.employer.sideMenu')
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-12 col-xs-12">

                        @if (!empty($arr->horizontal_ads))
                            <div class="bg-gray">
                                <div class="container">
                                    <div class="row">
                                        <div class="card bg-dark" style="width: 100%; height: 200px;">
                                            <div class="card-body">
                                                {!! $arr->horizontal_ads !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="job-alerts-item">
                            @if (Route::currentRouteName() == 'employer.dashboard')
                                <h3 class="alerts-title">{{ __('messages.JobLists') }}</h3>
                            @else
                                <h3 class="alerts-title">{{ __('messages.ArchiveJobs') }}</h3>
                            @endif

                            @if (count($jobs) > 0)
                                @foreach ($jobs as $job)
                                    @php
                                        $employeStatus = json_decode($job->employment_status);
                                    @endphp
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <a href="{{ route('job-details.show', $job->slug) }}">
                                                        <h6 class="mb-0 text-success">{{ $job->title }}</h6>
                                                    </a>

                                                    <p>{{ $job?->company?->company_name }}</p>
                                                    <p><i class="lni-map-marker" aria-hidden="true"></i>
                                                        {{ $job?->country?->name }}
                                                    </p>
                                                    <p> <i class="lni lni-graduation"></i>
                                                        {{ $job?->degree_level?->name }} {{ $job?->degree_name?->name }}</p>
                                                </div>

                                                <div class="col-md-6 text-right">
                                                    <img class="img-thumbnail"
                                                        src="{{ asset('assets/images/profilephoto/' . $job?->company?->photo) }}"
                                                        alt="{{ $job?->title }}">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-0"> <i class="lni lni-briefcase"></i>
                                                        {{ $job?->minimum_year }} {{ __('messages.to') }}
                                                        {{ $job?->maximum_year }} {{ __('messages.years') }}</p>
                                                </div>
                                                <div class="col-md-6 text-right">
                                                    <p> {{ __('messages.Deadline') }} : {{ $job?->application_deadline }}
                                                    </p>
                                                </div>
                                                <div class="col-lg-12">
                                                    {{ __('messages.Status') }}: @if ($job->is_publish == 1)
                                                        <span class="text-primary">{{ __('messages.Published') }} </span>
                                                    @else
                                                        <span class="text-danger">{{ __('messages.UnPublished') }} </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-inline-flex">
                                                <a href="{{ route('jobs.edit', $job) }}" class=" mr-3">
                                                    <button
                                                        class="mt-3 btn btn-success btn-sm">{{ __('messages.UpdateJobs') }}
                                                    </button>
                                                </a>
                                                <a href="{{ route('view.application', $job) }}" title="view application" class=" mr-3">
                                                    <button class="btn btn-primary mt-3 "> <i
                                                            class="fa-solid fa-eye"></i></button>
                                                </a>
                                                @if (Route::currentRouteName() != 'archive-jobs.index')
                                                    <form action="{{ route('jobs.destroy', $job) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="mt-3 btn btn-danger btn-sm">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('archive-jobs.restore', $job) }}"
                                                        method="POST">
                                                        @csrf
                                                        <button type="submit" class="mt-3 btn btn-danger btn-sm">
                                                            <i class="fa-solid fa-trash-can-arrow-up"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div>
                                    <p class="text-danger"> {{ __('messages.postedjobs') }}. </p>
                                </div>
                            @endif
                            {{-- <br> --}}
                            <!-- Start Pagination -->
                            {{ $jobs->links() }}
                            <!-- End Pagination -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
