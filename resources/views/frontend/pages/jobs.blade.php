@extends('frontend.layout.frontMaster')
@section('title', 'Find Jobs')
@section('content')
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="inner-header">
                        <h3>{{ __('messages.FindJob') }}</h3>
                    </div>
                    <div class="job-search-form">
                        <form action="{{ route('search-jobs') }}" method="GET">
                            @csrf
                            <div class="row">
                                <div class="col-lg-5 col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <input class="form-control" type="text" name="title"
                                            placeholder="{{ $title }}">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <div class="search-category-container">
                                            <label class="styled-select">
                                                <select name="country_id">
                                                    <option value="">{{ __('messages.Locations') }}</option>
                                                    @foreach ($locations as $location)
                                                        <option {{ $location?->id == $country_id ? 'selected' : '' }}
                                                            value="{{ $location?->id }}">{{ $location?->name }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                        </div>
                                        <i class="lni-map-marker"></i>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-xs-12">
                                    <div class="form-group">
                                        <div class="search-category-container">
                                            <label class="styled-select">
                                                <select name="category_id">
                                                    <option value="">{{ __('messages.AllCategories') }}</option>
                                                    @foreach ($job_categories as $category)
                                                        <option {{ $category->id == $category_id ? 'selected' : '' }}
                                                            value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                        </div>
                                        <i class="lni-layers"></i>
                                    </div>
                                </div>
                                <div class="col-lg-1 col-md-6 col-xs-12">
                                    <button type="submit" class="button"><i class="lni-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section id="job-listings" class="section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">{{ __('messages.Jobs') }}</h2>
            </div>
            <div class="row">
                @if (Count($jobs) > 0)
                    @foreach ($jobs as $job)
                        <div class="col-lg-6 col-md-12 col-xs-12">
                            <a class="job-listings-featured" href="{{ route('job-details', $job->id) }}">
                                <div class="row">
                                    <div class="col-lg-8 col-md-8 col-xs-12">
                                        <div class="job-company-logo">
                                            @if (optional($job->company)->photo)
                                                <img src="{{ asset('assets/images/profilephoto/' . $job->company->photo) }}"
                                                    alt="{{ optional($job->company)->company_name }}">
                                            @else
                                                <img src="https://placehold.co/100x100" alt="">
                                            @endif
                                        </div>

                                        <div class="job-details">
                                            <h3>{{ $job->title }}</h3>
                                            <span class="company-neme">{{ $job->company->company_name }}</span>
                                            <div class="tags">
                                                <span><i
                                                        class="lni-map-marker"></i>{{ $job->country ? $job->country->name : 'N/A' }}</span>
                                                <span><i class="lni-user"></i>{{ $job->vacancies }}</span>
                                                <span><i
                                                        class="lni-wallet"></i>{{ gs()->currency_symbol }}{{ $job->maximum_salary }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-xs-12 text-right">
                                        <div class="tag-type">
                                            @if (($status = getJobStatus($job->id)) === 0)
                                                <span class="heart-icon">
                                                    <img src="{{ asset('assets/images/placeholder/checkmark.svg') }}"
                                                        height="10" width="10">
                                                </span>
                                            @endif

                                            @forelse (employee_status($job->employment_status) as $value)
                                                <span class="full-time mb-2">{{ $value->name }}</span>
                                            @empty
                                                <span class="full-time">{{ __('messages.NA') }}</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                @else
                    <div class="text-center">
                        <p class="text-danger"> <strong> {{ __('messages.Nodatafound') }}</strong></p>
                    </div>

                @endif




                <div class="col-12 text-center mt-4">
                    {!! $jobs->links() !!}
                </div>
            </div>
        </div>
    </section>
@endsection
