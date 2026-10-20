@extends('dashboard.layout.dasboardMaster')
@section('title', 'Employer Job History')
@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="mb-0">{{ __('Company Lists') }}</h4>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-warning"> Back </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table-scroll">
                                <table id="zero_config" class="table border table-striped table-bordered">
                                    <thead>
                                        <th>SL</th>
                                        <th>Company Name </th>
                                        <th>Company Size </th>
                                        <th>Company Address </th>
                                        <th>Total Jobs </th>

                                        <th>Action</th>
                                    </thead>
                                    <tbody>
                                        @foreach ($jobs as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->company_name }}</td>
                                                <td>{{ $item?->company_sizes?->title }}</td>
                                                <td>{{ $item->company_address }}</td>
                                                <td>{{ $item->jobs_count }}</td>
                                                <td>
                                                    <div class="d-inline-flex">
                                                        <a href="{{ route('company-jobs', $item->id) }}">
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
        </div>
    </section>
@endsection