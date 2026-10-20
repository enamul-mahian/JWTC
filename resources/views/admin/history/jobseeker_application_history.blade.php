@extends('dashboard.layout.dasboardMaster')
@section('title', 'User Lists')
@section('content')
<section class="datatables">
    <!-- basic table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4 class="mb-0">{{ __('Job Seeker Lists') }}</h4>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-warning"> Back </a>
                </div>
                <div class="card-body table-responsive">
                    <div class="table-scroll">
                        <table id="zero_config" class="table border table-striped table-bordered text-nowrap">
                            <thead>
                                <!-- start row -->
                                <tr>
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Application </th>
                                    <th>Action </th>
                                </tr>
                                <!-- end row -->
                            </thead>
                            <tbody>
                                @foreach ($users as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->name ?? ''  }}</td>
                                        <td>{{ $item->email ?? '' }}</td>
                                        <td>{{ $item->applications_count }}</td>
                                        <td>
                                            <div class="d-inline-flex">
                                                <a href="{{ route('user-applied-jobs', $item->id) }}">
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