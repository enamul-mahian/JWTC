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
                    <a href="{{ route('jobseeker-history') }}" class="btn btn-sm btn-warning"> Back </a>
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
                                    <th>Application Date</th>
                                    <th>Job Category</th>
                                </tr>
                                <!-- end row -->
                            </thead>
                            <tbody>
                                @foreach ($applications as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->job->title ?? '' }}</td>
                                        <td>{{ $item?->job?->country?->name ?? '' }}</td>
                                        <td>{{ Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>
                                        <td>{{ $item->job->job_category->name ?? '' }}</td>
                                        
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