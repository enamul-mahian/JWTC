@extends('frontend.layout.frontMaster')
@section('title', 'Job Details')

@push('style')
    <style>
        .shortList {
            display: inline;
            border: 1px solid #00a5bf;
            border-radius: 3px;
            margin-right: 12px;
            padding: 4px 17px;
            cursor: pointer;
        }

        .share {
            display: inline;
            border: 1px solid #00a5bf;
            border-radius: 3px;
            margin-right: 3px;
            padding: 4px 17px;
            cursor: pointer;
        }

        .share i {
            margin-right: 3px;
        }

        .whatsapp {
            color: #25D366;
        }

        .facebook {
            color: #4267B2;
        }

        .linkedin {
            color: #0077b5;
        }

        .jobDetails_header {
            font-size: 17px !important;
            color: #a500b9 !important;
            margin-bottom: 11px !important;
        }

        .skill-btn {
            display: inline;
            border: 1px solid info
        }

        .skill-btn {
            display: inline;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-right: 5px;
            padding: 2px 6px;
            background: #e9e9e9cc;
        }

        .apply-btn {
            padding: 7px 15px;
            border: 1px solid #18b4cd;
            border-radius: 3px;
            background: #17a2b8;
            color: #fff;
            font-size: 17px;
            font-weight: 500;
            display: inline;
        }

        .apply_procedure {
            color: #a500b9 !important;
        }

        .report_section {
            border: 1px solid red !important;
        }

        .report_section h4 {
            font-size: 19px !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('page-style')
    <link rel="stylesheet" href="{{ asset('theme/job-details-layout.css') }}?v=20261026-2">
@endpush

@section('content')
    <!-- Job Detail Section Start -->
    <section class="job-detail section jg-job-details">
        <div class="container">
            <div class="row justify-content-between jg-job-detail-grid">
                <aside class="col-lg-3 col-md-12 col-xs-12 jg-detail-sidebar" aria-label="Employer account menu">
                    @include('frontend.pages.employer.sideMenu')
                </aside>

                <div class="col-lg-9 col-md-12 col-xs-12 jg-detail-main">
                    <div class="content-area">
                        <div class="row jg-job-hero">
                            <div class="col-md-12 col-sm-12 col-lg-6">
                                <h4 class="text-info mb-2">{{ $jobDetails?->title }}</h4>
                                <h6>{{ $jobDetails?->company?->company_name }}</h6>
                                @if($jobDetails?->location_detail)<p>{{ $jobDetails->location_detail }}</p>@endif
                                @if($jobDetails?->external_apply_url)<p><a href="{{ $jobDetails->external_apply_url }}" target="_blank" rel="noopener noreferrer" class="apply-btn">Apply on employer website</a></p>@endif
                                @if($jobDetails?->source_url)<p><a href="{{ $jobDetails->source_url }}" target="_blank" rel="noopener noreferrer">Original job listing</a></p>@endif
                                @if($jobDetails?->publicPosterEmail())
                                    <a class="jg-poster-email" href="mailto:{{ $jobDetails->publicPosterEmail() }}">{{ $jobDetails->publicPosterEmail() }}</a>
                                @endif
                                <p>{{ __('messages.ApplicationDeadline') }}:
                                    <strong>{{ Carbon\Carbon::parse($jobDetails?->application_deadline)->format('d M Y') }}
                                    </strong>
                                </p>
                            </div>
                            <div class="col-md-12 col-sm-12 col-lg-6 text-sm-center text-md-right text-lg-right">
                                <img style="width: 150px; height:100px"
                                    src="{{ asset('assets/images/profilephoto/' . $jobDetails?->company?->photo) }}"
                                    alt="{{ $jobDetails?->company?->company_name }}">
                            </div>

                            {{-- <div class="col-lg-12 text-right">
                                <span class="shortList"> <i class="fa-solid fa-star"></i> {{ __('shortlist') }} </span>
                                <span class="share">
                                    <span> <i class="fa-solid fa-share-nodes"></i> </span>
                                    <span> share </span>
                                    <span class="facebook"> <i class="fa-brands fa-facebook-f"></i> </span>
                                    <span class="linkedin"> <i class="fa-brands fa-linkedin-in"></i> </span>
                                    <span class="whatsapp"> <i class="fa-brands fa-whatsapp"></i> </span>
                                </span>
                            </div> --}}

                        </div>
                        <div class="card mt-4 jg-summary-card">
                            <div class="card-body">
                                <h5 class=" jobDetails_header mb-1 text-danger">{{ __('messages.Summary') }}</h5>
                                <div class="row">
                                    <div class="col-lg-4 col-md-4 col-sm-6">
                                        <p class="mb-1">{{ __('messages.Vacancy') }}: {{ $jobDetails?->vacancies }}</p>
                                        <p class="mb-1">{{ __('messages.Salary') }}:
                                            {{ \App\Services\JobPresentation::salarySummary($jobDetails) }}</p>
                                        @if($equivalents = \App\Services\JobPresentation::salaryEquivalents($jobDetails))
                                            <div class="mt-2" style="line-height:1.8">
                                                <div>Monthly gross equivalent: {{ $equivalents['monthly'] }}</div>
                                                <div>Weekly gross equivalent: {{ $equivalents['weekly'] }}</div>
                                                <div>Hourly gross equivalent ({{ $equivalents['hours'] }} hours/week): {{ $equivalents['hourly'] }}</div>
                                                <small>Payment frequency: To Be Confirmed with employer. Equivalents are estimates, not confirmed payment arrangements or take-home pay.</small>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-6">
                                        <p class="mb-1">{{ __('messages.Age') }}:
                                            @if ($jobDetails?->mini_age && $jobDetails?->max_age)
                                                {{ $jobDetails?->mini_age }} to {{ $jobDetails?->max_age }}
                                            @elseif ($jobDetails?->mini_age)
                                                At least {{ $jobDetails?->mini_age }}
                                            @elseif ($jobDetails?->max_age)
                                                At least {{ $jobDetails?->max_age }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                        <p class="mb-1">{{ __('messages.Experience') }}:
                                            {{ $jobDetails?->minimum_year . '-' . $jobDetails?->maximum_year }} years
                                        </p>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-6">
                                        <p class="mb-1">Location: {{ $jobDetails?->country?->name }} </p>
                                        <p class="mb-1">{{ __('messages.Publishedon') }}:
                                            @if ($jobDetails->approval_date != null)
                                                {{ Carbon\Carbon::parse($jobDetails?->approval_date)->format('d M Y') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4 jg-content-card">
                            <div class="card-body">
                                <h5 class=" jobDetails_header text-danger"> {{ __('messages.JobResponsibilities') }}</h5>
                                <p>{!! $jobDetails?->job_responsibilities . ' ' . $jobDetails?->job_context !!} </p>
                            </div>
                        </div>
                        <div class="card mt-4 jg-content-card">
                            <div class="card-body">
                                <h5 class=" jobDetails_header text-danger">{{ __('messages.ExperienceRequirements') }}</h5>
                                <ul class="list-group ml-3">
                                    <li class="mb-0">
                                        @if ($jobDetails?->minimum_year && $jobDetails?->maximum_year)
                                            {{ $jobDetails?->minimum_year . ' - ' . $jobDetails?->maximum_year }} years
                                        @endif
                                    </li>
                                    @if ($jobDetails->fresher)
                                        <li class="mb-0">-
                                            {{ __('messages.Freshers') }}
                                        </li>
                                    @endif
                                </ul>

                            </div>
                        </div>
                        <div class="card mt-4 jg-content-card">
                            <div class="card-body">
                                <h5 class=" jobDetails_header text-danger">{{ __('messages.EducationalRequirements') }}</h5>
                                <ul>
                                    <li class="mb-2">
                                        {{ $jobDetails?->degree_level?->name }} {{ $jobDetails?->degree_name?->name }}
                                    </li>

                                    <li class="mb-0">-
                                        {{ __('messages.Skills') }}:
                                        @foreach ($skills as $skill)
                                            <span class="skill-btn">{{ $skill->skill_name }}</span>
                                        @endforeach
                                    </li>
                                </ul>

                                <h5 class=" jobDetails_header text-danger">{{ __('messages.Age') }}</h5>
                                <ul>
                                    <li class="mb-0">
                                        @if ($jobDetails?->mini_age && $jobDetails?->max_age)
                                            {{ $jobDetails?->mini_age . ' - ' . $jobDetails?->max_age }} Age
                                        @elseif ($jobDetails?->mini_age)
                                            {{ $jobDetails?->mini_age }} Age
                                        @elseif($jobDetails?->max_age)
                                            {{ $jobDetails?->max_age }} Age
                                        @else
                                            N/A
                                        @endif
                                    </li>
                                </ul>

                            </div>
                        </div>
                        <div class="card mt-4 jg-content-card">
                            <div class="card-body">
                                <h5 class=" jobDetails_header text-danger">{{ __('messages.AdditionalRequirements') }} </h5>
                                <ul>
                                    <li class="mb-0">{!! $jobDetails->additional_requirements !!}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="card mt-4 jg-content-card">
                            <div class="card-body">
                                <h5 class=" jobDetails_header text-danger">{{ __('messages.Compensation') }}</h5>
                                <ul>
                                    <li class="mb-0">
                                        @if (isset($other_benefits))
                                            -
                                            @foreach ($other_benefits as $benefits)
                                                <span>{{ ucwords(str_replace('_', ' ', $benefits)) }}</span>
                                                @if (!$loop->last)
                                                    <span>,</span>
                                                @endif
                                            @endforeach
                                        @endif
                                    </li>
                                    <li class="mb-0">-
                                        {{ __('messages.SalaryReview') }}:
                                        {{ ucWords(str_replace('_', ' ', $jobDetails->salary_review)) }}
                                    </li>
                                </ul>

                                <h5 class=" jobDetails_header text-danger">{{ __('messages.Workplace') }}</h5>
                                <ul>
                                    <li class="mb-0">
                                        @if (isset($workPlaces))
                                            @foreach ($workPlaces as $workPlace)
                                                <span> {{ ucWords(str_replace('_', ' ', $workPlace)) }} </span>
                                                @if (!$loop->last)
                                                    <span>,</span>
                                                @endif
                                            @endforeach
                                        @endif

                                    </li>
                                </ul>

                                <h5 class=" jobDetails_header text-danger">{{ __('messages.EmploymentStatus') }}</h5>
                                <ul>
                                    <li class="mb-0">
                                        @if (isset($jobDetails->employment_status))
                                            @foreach (json_decode($jobDetails->employment_status) as $status)
                                                <span> {{ ucwords(str_replace('_', ' ',$status)) }} </span>
                                                @if (!$loop->last)
                                                    <span>,</span>
                                                @endif
                                            @endforeach
                                        @endif
                                    </li>
                                </ul>
                                @if (isset($genders))
                                    <h5 class=" jobDetails_header text-danger">{{ __('messages.Gender') }}</h5>
                                    <ul>
                                        <li class="mb-0">
                                            @foreach ($genders ?? [] as $gender)
                                                <span> {{ ucWords(str_replace('_', ' ', $gender)) }} </span>
                                                @if (!$loop->last)
                                                    <span>,</span>
                                                @endif
                                            @endforeach
                                        </li>
                                    </ul>
                                @endif

                                <h5 class=" jobDetails_header text-danger">{{ __('messages.JobLocation') }}</h5>
                                <ul>
                                    <li class="mb-0"> {{ ucwords($jobDetails?->country?->name) }} </li>
                                </ul>
                                
                            </div>
                        </div>

                        <div class="card mt-4 jg-company-card">
                            <div class="card-body py-1">
                                <h5 class="apply_procedure">{{ __('messages.CompanyInformation') }}</h5>
                                <p class="mb-0">{{ $jobDetails?->company?->company_name }}</p>
                                <p class="mb-0">{{ $jobDetails?->company?->company_address }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Job Detail Section End -->
@endsection
