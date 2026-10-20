@extends('dashboard.layout.dasboardMaster')
@section('title', 'User Subscription')

@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5> @yield('title') </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table-scroll">
                                <table id="zero_config" class="table border table-striped table-bordered">
                                    <thead>
                                        <!-- start row -->
                                        <tr>
                                            <th>SL</th>
                                            <th>Name</th>
                                            <th>Plan Name</th>
                                            <th>Plan Type</th>
                                            <th>Amount</th>
                                            <th>Expire Date</th>
                                        </tr>
                                        <!-- end row -->
                                    </thead>
                                    <tbody>
                                        @foreach ($features as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ subscriptionUser($item->subscriber_id) }}</td>
                                                <td>{{ $item?->plan?->name }}</td>
                                                <td>{{ ucwords(str_replace('_', ' ', $item?->plan?->plan_type)) }}</td>
                                                <td>{{ $item?->plan?->price }}</td>
                                                <td>{{ Carbon\Carbon::now()->gt(subscriptionExpireDate($item->id)) ? 'Expired' : Carbon\Carbon::parse(subscriptionExpireDate($item->id))->format('d M Y') }}
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
@push('script')
    <script>
        $('.delete-btn').on('click', function() {
            let id = $(this).attr('data-id')
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#delete_form_' + id).submit()
                }
            })
        })
    </script>
@endpush
