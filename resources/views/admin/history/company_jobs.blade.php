@extends('dashboard.layout.dasboardMaster')
@section('title', 'Job Lists')
@section('content')
<section class="datatables">
    <!-- basic table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4 class="mb-0">{{ __('Job Lists') }}</h4>
                    <a href="{{ route('employer-history') }}" class="btn btn-sm btn-warning"> Back </a>
                </div>
                <div class="card-body">
                    <div class="table-scroll">
                        <table id="zero_config" class="table border table-striped table-bordered text-nowrap">
                            <thead>
                                <!-- start row -->
                                <tr>
                                    <th>SL</th>
                                    <th>Job Title </th>
                                    <th>Job Location </th>
                                    <th>Job Category</th>
                                    <th>Total Applications </th>
                                    <th>Action</th>
                                </tr>
                                <!-- end row -->
                            </thead>
                            <tbody>
                                @foreach ($jobs as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->title }}</td>
                                        <td>{{ $item->country->name ?? '' }}</td>
                                        <td>{{ $item->job_category->name ?? '' }}</td>
                                        <td>{{ $item->applications->count() }}</td>
                                        <td>
                                            <div class="d-inline-flex">
                                                <a href="{{ route('job-applied-users', $item->id) }}">
                                                    <button class="btn btn-primary btn-sm me-2">
                                                        <i class="fa-regular fa-eye"></i>
                                                    </button>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection