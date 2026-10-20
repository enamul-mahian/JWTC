@extends('frontend.layout.frontMaster')
@Section('title', 'Job Details')
@Section('content')
    @include('modal.common')
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-6 col-xs-12">
                    <div class="breadcrumb-wrapper">
                        <div class="img-wrapper">
                            @if (optional($job->company)->photo)
                                <img src="{{ asset('assets/images/profilephoto/' . $job->company->photo) }}"
                                    alt="{{ optional($job->company)->company_name }}" style="width: 100%">
                            @else
                                <img src="https://placehold.co/100x100" alt="{{ optional($job->company)->company_name }}">
                            @endif
                        </div>

                        <div class="content">
                            <h3 class="product-title">{{ $job->title }}</h3>
                            <p class="brand">{{ $job->company ? $job->company->company_name : null }}</p>
                            <div class="tags">
                                <span><i
                                        class="lni-map-marker"></i>{{ $job->country ? $job->country->name : 'N/A' }}</span>
                                <span><i class="lni-calendar"></i> {{ __('messages.Posted') }}
                                    {{ dateFormat($job->created_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-xs-12">
                    <div class="month-price">
                        <span class="year">{{ $job->salary_type }}</span>
                        <div class="price">
                            <span>{{ gs()->currency_symbol }}{{ $job->minimum_salary }} -
                                {{ gs()->currency_symbol }}{{ $job->maximum_salary }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="job-detail section">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-lg-12 col-md-12 col-xs-12">

                    <div class="content-area">
                        @if (($status = getJobStatus($job->id)) === 0)
                            <span class="heart-icon text-danger">
                                <img src="{{ asset('assets/images/placeholder/checkmark.svg') }}" height="10"
                                    width="10"> {{ __('messages.AlreadyApplied') }}
                            </span>
                        @elseif(($status = getJobStatus($job->id)) === 1)
                            <span class="heart-icon text-success">
                                <img src="{{ asset('assets/images/placeholder/checkmark.svg') }}" height="10"
                                    width="10"> {{ __('messages.Shortlisted') }}
                            </span>
                        @endif

                        <h5>{{ __('messages.Summary') }} </h5>
                        <div class="row">
                            <div class="col-sm-4 col-xs-4">
                                <span class="text-bold">{{ __('messages.Vacancy') }}: {{ $job->vacancies }}</span><br>
                                <span
                                    class="text-bold">{{ __('messages.Salary') }}:{{ gs()->currency_symbol }}{{ $job->minimum_salary }}
                                    -
                                    {{ gs()->currency_symbol }}{{ $job->maximum_salary }}</span>
                            </div>
                            <div class="col-sm-4 col-xs-4">
                                <span>{{ __('messages.Age') }}: {{ $job->mini_age }} to {{ $job->max_age }}
                                    {{ __('messages.years') }}</span> <br>
                                <span>{{ __('messages.Experience') }}: {{ $job->minimum_year }} to
                                    {{ $job->maximum_year }} {{ __('messages.years') }}</span> <br>
                                @if ($job->fresher)
                                    <span>{{ __('messages.Freshersarealsoencouragedtoapply') }}.</span>
                                @endif
                            </div>
                            <div class="col-sm-4 col-xs-4">
                                @php
                                    $gender = $job->gender;
                                    if ($gender) {
                                        $genders = json_decode($job->gender);
                                    } else {
                                        $genders = [];
                                    }
                                @endphp

                                <span>{{ __('messages.Gender') }}:
                                    @forelse ($genders ?? [] as $key => $value)
                                        <span>{{ ucwords( $value) }} @if (!$loop->last)
                                                ,
                                            @endif </span>
                                    @empty
                                        <span>{{ __('messages.NA') }}</span>
                                    @endforelse
                                </span> <br>
                                <span>{{ __('messages.Deadline') }}: {{ $job->application_deadline }} </span>
                            </div>

                        </div>
                    </div>

                    <div class="content-area mt-2">
                        <h5>{{ __('messages.JobResponsibility') }}</h5>
                        <div>
                            {!! $job->job_responsibilities !!}
                        </div>
                    </div>

                    <div class="content-area mt-2">
                        <h5 class="mt-3">{{ __('messages.JobContext') }}</h5>
                        <div>
                            {!! $job->job_context !!}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="content-area mt-2">
                                @php
                                    $works = $job->work_place;
                                    if ($works) {
                                        $work = json_decode($job->work_place);
                                    } else {
                                        $work = [];
                                    }
                                @endphp
                                <h5 class="mt-3">{{ __('messages.Workplace') }}</h5>
                                @forelse ($work as $key => $value)
                                    <span>{{ ucwords(str_replace('_', ' ', $value)) }}
                                        @if (!$loop->last)
                                            ,
                                        @endif
                                    </span>
                                @empty
                                    <span>{{ __('messages.NA') }}</span>
                                @endforelse
                            </div>
                            <div class="content-area mt-2">
                                <h5>{{ __('messages.SalaryReview') }} </h5>
                                <span>{{ ucwords(str_replace('_', ' ', $job->salary_review)) }}</span>
                                <p>{!! $job->additional_salary_info !!}</p>
                            </div>
                            <div class="content-area mt-2">
                                <h5>{{ __('messages.FestivalBonus') }} </h5>
                                <span>{{ $job->festival_bonus }}</span>
                            </div>
                            <div class="content-area mt-2">
                                <h5>{{ __('messages.LunchFacilities') }} </h5>
                                <span>{{ ucwords(str_replace('_', ' ', $job->lunch_facilities)) }}</span>
                            </div>
                        </div>

                        {{-- <div class="col-md-6">
                            <div class="bg-gray">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-12 p-3">
                                            <div class="card bg-dark" style="width: 100%; height: 230px;">
                                                <div class="card-body">
                                                    {!! $arr->square_ads ?? 'No square ads available' !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>

                    <div class="content-area mt-2">
                        <h5>{{ __('messages.AdditionalInfo') }} </h5>
                        @php
                            $skills = json_decode($job->skills) ?? ['N/A'];
                            $others = json_decode($job->other_benefits) ?? ['N/A'];
                            $educations = json_decode($job->job_level) ?? ['N/A'];
                        @endphp

                        <span><b>{{ __('messages.EducationsRequirements') }}:</b>
                            @foreach ($educations as $key => $value)
                                {{ ucfirst($value) }}
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </span>
                        <br>

                        <span><b>{{ __('messages.Skills') }}:</b>
                            @foreach ($skills as $key => $value)
                                {{ ucfirst(getSkillName($value)) }}
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </span>
                        <br>

                        <span><b>{{ __('messages.OtherBenefits') }}:</b>
                            @foreach ($others as $key => $value)
                                {{ ucwords(str_replace('_', ' ', $value)) }}
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </span>
                    </div>

                    <div class="content-area mt-2">
                        <h5 class="mt-3">{{ __('messages.EmploymentStatus') }}</h5>
                        @forelse (employee_status($job->employment_status) as  $value)
                            <span>{{ ucwords(str_replace('_', ' ', $value->name)) }}
                                @if (!$loop->last)
                                    ,
                                @endif
                            </span>
                        @empty
                            <span>{{ __('messages.NA') }}</span>
                        @endforelse
                    </div>
                    @php
                        $status = getJobStatus($job->id);
                    @endphp
                    @if ($status === 0 || $status === 2 || $status === 3)
                    @else
                        <div class="content-area mt-2">
                            <a href="{{ route('get-application-form', $job->id) }}"
                                class="btn btn-common mt-2 btn_modal">{{ __('messages.Applyjob') }}</a>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>
@endsection

@push('script')
    <script src="{{ asset('frontend/assets/script/application.js') }}"></script>
@endpush
