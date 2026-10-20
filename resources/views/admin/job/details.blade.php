@extends('dashboard.layout.dasboardMaster')
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

        .content-area {
            border: 1px solid black !important;
            padding: 8px !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="mb-0">{{ __('Job Details') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="col-lg-10 col-md-12 col-xs-12">
                            <div class="content-area">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12 col-lg-6">
                                        <h4 class="text-info mb-2">{{ $jobDetails?->title }}</h4>
                                        <h6>{{ $jobDetails?->company?->company_name }}</h6>
                                        <p>{{ __('Application Deadline:') }}
                                            <strong>{{ Carbon\Carbon::parse($jobDetails?->application_deadline)->format('d M Y') }}
                                            </strong>
                                        </p>
                                    </div>
                                    <div class="col-md-12 col-sm-12 col-lg-6 text-sm-center text-md-right text-lg-right">
                                        <img style="width: 150px; height:100px"
                                            src="{{ asset('assets/images/profilephoto/' . $jobDetails?->company?->photo) }}"
                                            alt="{{ $jobDetails?->company?->company_name }}">
                                    </div>

                                    <div class="col-lg-12 text-right">
                                        <span class="shortList"> <i class="fa-solid fa-star"></i> {{ __('shortlist') }}
                                        </span>
                                        <span class="share">
                                            <span> <i class="fa-solid fa-share-nodes"></i> </span>
                                            <span> share </span>
                                            <span class="facebook"> <i class="fa-brands fa-facebook-f"></i> </span>
                                            <span class="linkedin"> <i class="fa-brands fa-linkedin-in"></i> </span>
                                            <span class="whatsapp"> <i class="fa-brands fa-whatsapp"></i> </span>
                                        </span>
                                    </div>
                                </div>
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <h5 class=" jobDetails_header mb-1 text-danger">{{ __('Summary') }}</h5>
                                        <div class="row">
                                            <div class="col-lg-4 col-md-4 col-sm-6">
                                                <p class="mb-1">{{ __('Vacancy:') }} {{ $jobDetails?->vacancies }}</p>
                                                <p class="mb-1">{{ __('Salary:') }}
                                                    {{ $jobDetails?->minimum_salary . ' - ' . $jobDetails?->maximum_salary }}
                                                </p>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-6">
                                                <p class="mb-1">{{ __('Age:') }}
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
                                                <p class="mb-1">{{ __('Experience:') }}
                                                    {{ $jobDetails?->minimum_year . '-' . $jobDetails?->maximum_year }}
                                                    years
                                                </p>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-6">
                                                <p class="mb-1">Location: {{ $jobDetails?->location?->name }} </p>
                                                <p class="mb-1">{{ __('Published on:') }}
                                                    @if ($jobDetails->created_at != null)
                                                        {{ Carbon\Carbon::parse($jobDetails?->created_at)->format('d M Y') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-body">
                                        <h5 class=" jobDetails_header text-danger">
                                            {{ __('Job Responsibilities & Context') }}</h5>
                                        <p>{!! $jobDetails?->job_responsibilities . ' ' . $jobDetails?->job_context !!} </p>
                                        <hr>
                                        <h5 class=" jobDetails_header text-danger">{{ __(' Experience Requirements') }}
                                        </h5>
                                        <ul class="list-group ml-3">
                                            <li class="mb-0">-
                                                @if ($jobDetails?->minimum_year && $jobDetails?->maximum_year)
                                                    {{ $jobDetails?->minimum_year . ' to ' . $jobDetails?->maximum_year }}
                                                    years
                                                @endif
                                            </li>
                                            @if ($jobDetails->fresher)
                                                <li class="mb-0"> -
                                                    {{ __('Freshers are also encouraged to apply.') }}
                                                </li>
                                            @endif
                                        </ul>

                                        <h5 class=" jobDetails_header text-danger">{{ __('Educational Requirements') }}
                                        </h5>
                                        <ul>
                                            <li class="mb-2">
                                                @if ($jobDetails?->degree_level?->name)
                                                    -
                                                @endif
                                                {{ $jobDetails?->degree_level?->name }}
                                                {{ $jobDetails?->degree_name?->name }}
                                            </li>

                                            <li class="mb-0">-
                                                {{ __(' Skills') }}:
                                                {{-- @php $skills = App\Models\Skill::whereIn('id', json_decode($jobDetails->skills))->get(); @endphp --}}
                                                @foreach ($skills as $skill)
                                                    <span class="skill-btn">{{ $skill->skill_name }}</span>
                                                @endforeach
                                            </li>
                                        </ul>

                                        <h5 class=" jobDetails_header text-danger">{{ __('Additional Requirements') }}
                                        </h5>
                                        <ul>
                                            <li class="mb-0">-
                                                @if ($jobDetails?->mini_age && $jobDetails?->max_age)
                                                    {{ $jobDetails?->mini_age . ' to ' . $jobDetails?->max_age }} years
                                                @elseif($jobDetails?->mini_age)
                                                    {{ $jobDetails?->mini_age }} years
                                                @elseif($jobDetails?->max_age)
                                                    {{ $jobDetails?->max_age }} years
                                                @else
                                                    N/A
                                                @endif
                                            </li>
                                            <li class="mb-0">{!! $jobDetails->additional_requirements !!}</li>
                                        </ul>
                                        <hr>
                                        @if (isset($workPlaces))
                                            <h5 class=" jobDetails_header text-danger">
                                                {{ __('Compensation & Other Benefits') }}</h5>
                                            <ul>
                                                <li class="mb-0">-
                                                    @foreach ($other_benefits as $benefits)
                                                        <span>{{ ucwords(str_replace('_', ' ', $benefits))  }}</span>
                                                        @if (!$loop->last)
                                                            <span>,</span>
                                                        @endif
                                                    @endforeach
                                                </li>
                                                <li class="mb-0">-
                                                    {{ __('Salary Review:') }}
                                                    {{ ucWords(str_replace('_', ' ', $jobDetails->salary_review)) }}
                                                </li>
                                            </ul>
                                        @endif

                                        @if (isset($workPlaces))
                                            <h5 class=" jobDetails_header text-danger">{{ __('Workplace') }}</h5>
                                            <ul>
                                                <li class="mb-0">
                                                    @foreach ($workPlaces as $workPlace)
                                                        <span> {{ ucWords(str_replace('_', ' ', $workPlace)) }} </span>
                                                        @if (!$loop->last)
                                                            <span>,</span>
                                                        @endif
                                                    @endforeach
                                                </li>
                                            </ul>
                                        @endif

                                        @if (isset($jobDetails->employment_status))
                                            <h5 class=" jobDetails_header text-danger">{{ __('Employment Status') }}</h5>
                                            <ul>
                                                <li class="mb-0">
                                                    @foreach (employee_status($jobDetails->employment_status) as $status)
                                                        <span> {{ ucWords(str_replace('_', ' ', $status->name)) }} </span>
                                                        @if (!$loop->last)
                                                            <span>,</span>
                                                        @endif
                                                    @endforeach
                                                </li>
                                            </ul>
                                        @endif

                                        @if (isset($genders))
                                            <h5 class=" jobDetails_header text-danger">{{ __('Gender') }}</h5>
                                            <ul>
                                                <li class="mb-0">
                                                    @foreach ($genders as $gender)
                                                        <span> {{ ucWords(str_replace('_', ' ', $gender)) }} </span>
                                                        @if (!$loop->last)
                                                            <span>,</span>
                                                        @endif
                                                    @endforeach
                                                </li>
                                            </ul>
                                        @endif

                                        <h5 class=" jobDetails_header text-danger">{{ __('Job Location') }}</h5>
                                        <ul>
                                            <li class="mb-0"> {{ $jobDetails?->location?->name }} </li>
                                        </ul>
                                    </div>
                                </div>

                                {{-- <div class="card mt-4">
                                    <div class="card-header py-1">
                                        <h5 class="apply_procedure mb-4">{{ __('Apply Procedure') }}</h5>
                                        <a href="#">
                                            <span class="apply-btn">Apply Now</span>
                                        </a>
                                        <p class="mt-4">Application Deadline:
                                            <strong>
                                                {{ Carbon\Carbon::parse($jobDetails?->application_deadline)->format('d M Y') }}
                                            </strong>
                                        </p>
                                    </div>
                                </div> --}}

                                <div class="card mt-4">
                                    <div class="card-body py-1">
                                        <h5 class="apply_procedure">Company Information</h5>
                                        <p class="mb-0">{{ $jobDetails?->company?->company_name }}</p>
                                        <p class="mb-0">{{ $jobDetails?->company?->company_address }}</p>
                                    </div>
                                </div>

                                <div class="card mt-4 ">
                                    <div class="card-header report_section">
                                        <h4 class="mb-1">Report this Job / Company (রিপোর্ট)</h4>
                                        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ut, illo tempora
                                            explicabo quis
                                            ipsum quae velit, incidunt porro amet error voluptatem corrupti, quos molestias
                                            sapiente.</p>
                                        <p class="mb-1">
                                            <a href="tel:16487">
                                                <i class="fa-solid fa-phone"></i> 16427
                                            </a>
                                        </p>
                                        <p class="mb-1">
                                            <a href="mailto:jobportal@job.com">
                                                <i class="fa-regular fa-envelope"></i> jobportal@job.com
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
