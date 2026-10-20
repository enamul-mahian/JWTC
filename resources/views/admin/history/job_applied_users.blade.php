@extends('dashboard.layout.dasboardMaster')
@section('title', 'User Lists')
@section('content')
<section class="datatables">
    <!-- basic table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4 class="mb-0">{{ __('User Lists') }}</h4>
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-warning"> Back </a>
                </div>
                <div class="card-body">
                    <div class="table-scroll">
                        <table id="zero_config" class="table border table-striped table-bordered text-nowrap">
                            <thead>
                                <!-- start row -->
                                <tr>
                                    <th>SL</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Application Date </th>
                                </tr>
                                <!-- end row -->
                            </thead>
                            <tbody>
                                @foreach ($users as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->user->name ?? ''  }}</td>
                                        <td>{{ $item->user->email ?? '' }}</td>
                                        <td>{{ dateFormat($item->created_at) }}</td>
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