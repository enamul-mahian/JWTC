@extends('frontend.layout.frontMaster')
@Section('title', 'Applied Jobs')
    @Section('content')
        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner-header">
                            <h3>{{ __('messages.ShortlistJobs') }}</h3>
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
                            <h4>{{ __('messages.ShortlistJobs') }}</h4>
                            @include('jobseeker.partials.sidenav')
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-8 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mt-3" id="applied_jobs_table"> </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endsection

        @push('script')
            <script>
            $(document).ready(function () {
                function getAppliedJobs(page) {
                    $.ajax({
                        url: "/job_seeker/get-all-shortlist-jobs?page=" + page,
                        type: "get",
                        datatype: "html",
                        success: function (data) {
                            $("#applied_jobs_table").html(data);
                        },
                    });
                }
                getAppliedJobs(1);

                
            });
            </script>
        @endpush
