@extends('frontend.layout.frontMaster')
@Section('title', 'Unique Code')
    @Section('content')

        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="inner-header">
                            <h3>Unique Code</h3>
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
                            <h4>Unique Code</h4>
                            @include('jobseeker.partials.sidenav')
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-8 col-xs-12">
                        <div class="card">
                            <div class="card-body">
<p>Each approved code allows one job application. Request another code to apply for more jobs. An administrator can approve multiple codes or reject your request.</p>
                                <div>
                                    @if ($canCreate > 0)
                                        <form method="POST" action="{{ route('request-code') }}" onsubmit="return confirm('Are you sure you want to send request?');">
                                            @csrf
                                            <button type="submit" class="btn btn-primary">Request New Code</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="row mt-3"> 
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>User</th>
                                                <th>Code</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($uniqueCodes as $uniqueCode)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $uniqueCode->user->name ?? 'N/A' }}</td>
                                                    <td>{{ $uniqueCode->code }}</td>
                                                    <td>{{ $uniqueCode->date }}</td>
                                                    <td>{{ $uniqueCode->code === null ? ($uniqueCode->is_used ? 'Rejected / Cancelled' : 'Pending approval') : ($uniqueCode->is_used ? 'Used' : 'Available') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endsection

        {{-- @push('script')
            <script>
            $(document).ready(function () {
                function getAppliedJobs(page) {
                    $.ajax({
                        url: "/job_seeker/get-all-applied-jobs?page=" + page,
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
        @endpush --}}
