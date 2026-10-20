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
                        <div class="job-alerts-item">
                            <h3 class="alerts-title">{{ __('messages.ManageJobLists') }}</h3>
                            @foreach ($counts as $job)
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
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <h6 class="mb-0 text-success">
                                                    {{ isset($job) && $job->approval_date != null ? __('messages.Approved') : __('messages.Disapproved') }}
                                                </h6>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <p class="mb-0">
                                                    {{ __('messages.ApproveDate') }}:
                                                    {{ Carbon\Carbon::parse($job?->approval_date)->format('d M Y') }}
                                                </p>
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <p> {{ __('messages.Deadline') }} : {{ $job?->application_deadline }}</p>
                                            </div>
                                            <div class="col-lg-6">
                                                <a href="{{ route('view.application', $job->id) }}">
                                                    {{ __('messages.DroppedCv') }}: {{ $job?->applications_count }}
                                                </a>
                                            </div>
                                            <div class="col-lg-6 text-right">
                                                {{ __('messages.Status') }}: @if ($job->is_publish == 1)
                                                    <span class="text-primary">{{ __('messages.Published') }} </span>
                                                @else
                                                    <span class="text-danger">{{ __('messages.UnPublished') }} </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            {{-- <br> --}}
                            <!-- Start Pagination -->
                            {{ $counts->links() }}
                            <!-- End Pagination -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
