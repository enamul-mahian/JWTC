@extends('frontend.layout.frontMaster')

@section('title', __('Job Analytics'))
@push('style')
    <style>
        .analytic_card {
            height: 140px !important;
        }
    </style>
@endpush
@section('content')

    <!-- Page Header Start -->
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="inner-header">
                        <h3>{{ __('messages.BrowseJob') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Job Browse Section Start -->
    <section class="job-browse section">
        <div class="container">
            <div class="row">

                <div class="col-lg-3 col-md-12 col-xs-12">
                    @include('frontend.pages.employer.sideMenu')
                </div>

                <div class="col-lg-9  col-md-12 col-xs-12">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-xs-12 mb-4 mb-lg-0">
                            <div class="job-listings-featured h-100 ">
                                <h6>{{ __('messages.JOBS') }}</h6>
                                <hr>
                                <table class="table table-borderless ">
                                    <tbody>
                                        <tr>
                                            <td class="py-1">{{ __('messages.TotalJob') }}</td>
                                            <td class="py-1">{{ count($jobsId)  }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-xs-12 mb-4 mb-lg-0">
                            <div class="job-listings-featured h-100 ">
                                <h6>{{ __('messages.APPLICATIONS') }}</h6>
                                <hr>
                                <table class="table table-borderless ">
                                    <tbody>
                                        <tr>
                                            <td class="py-1">{{ __('messages.ApplicationsJob') }}</td>
                                            <td class="py-1">{{ $applicationJob }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1">{{ __('messages.ShortListedJob') }}</td>
                                            <td class="py-1">{{ $shortListedJob }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-1">{{ __('messages.ShortListed') }}</td>
                                            <td class="py-1">{{ $interViewJob }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-6 col-md-6 col-xs-12 mb-4 mb-lg-0">
                            <div class="job-listings-featured h-100 ">
                                <h6>{{ __('messages.GENDER') }}</h6>
                                <hr>
                                <div id="piechart"></div>
                            </div>
                        </div>
                        {{-- <div class="col-lg-6 col-md-6 col-xs-12 mb-4 mb-lg-0">
                            <div class="job-listings-featured h-100 ">
                                <h6>AGE</h6>
                                <hr>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Job Browse Section End -->
@endsection

@push('script')
    <script src="{{ asset('assets/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        function getJobCategoryData() {
            fetch('employer/job-analytics-chart-data')
                .then(response => response.json())
                .then(data => {
                    const options = {
                        chart: {
                            type: 'pie',
                        },
                        labels: ['Male', 'Female'],
                        series: [data.male, data.female],
                    };

                    const chart = new ApexCharts(document.querySelector("#piechart"), options);
                    chart.render();
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                });
        }

        setTimeout(getJobCategoryData, 2000);
    </script>
@endpush
