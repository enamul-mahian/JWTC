@extends('dashboard.layout.dasboardMaster')
@section('title', 'Job Seeker')
@push('style')
    <style>
        .form-check-input:focus {
            border-color: #98b3f5;
            outline: 0;
            -webkit-box-shadow: none !important;
            box-shadow: none !important;
        }

        .form-check-input {
            background-color: red;
            border-color: red;
        }

        .form-switch .form-check-input {
            width: 2em;
            margin-left: -2.5em;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e") !important;
            background-position: left center;
            border-radius: 2em;
        }
    </style>
@endpush
@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="mb-0">{{ __('Job Seeker') }}</h4>
                        {{-- <a href="{{ route('job-categories.create') }}">
                            <button class="btn btn-outline-primary"><i class="fa-solid fa-plus me-2"></i> Create</button>
                        </a> --}}
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table-scroll">
                                <table id="zero_config" class="table border table-striped table-bordered text-nowrap">
                                    <thead>
                                        <!-- start row -->
                                        <tr>
                                            <th>SL</th>
                                            <th>Name</th>
                                            <th>Image</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Status</th>
                                            @can('inactive-job')
                                                <th>Action</th>
                                            @endcan
                                        </tr>
                                        <!-- end row -->
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->email }}</td>
                                                <td>{{ $item->phone }}</td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" value="{{ $item->id }}"
                                                            type="checkbox" name="status" <?php echo $item->status == 1 ? 'checked' : ''; ?>
                                                            role="switch" id="flexSwitchCheckDefault">
                                                    </div>
                                                </td>
                                                @can('inactive-job')
                                                    <td>
                                                        <div class="d-inline-flex">
                                                            <a href="{{ route('jobseeker.show', $item->id) }}">
                                                                <button class="btn btn-primary me-2">
                                                                    <i class="fa-regular fa-eye"></i>
                                                                </button>
                                                            </a>
                                                        </div>
                                                    </td>
                                                @endcan

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
@if (Session::has('message'))
    @push('script')
        <script>
            Swal.fire({
                position: "top-end",
                icon: "<?php echo session('class'); ?>",
                title: "<?php echo session('message'); ?>",
                showConfirmButton: false,
                timer: 1500,
                toast: true
            });
        </script>
    @endpush
@endif

@push('script')
    <script>
        $("input[name=status]").on('change', function() {
            let value = $(this).prop('checked') == true ? 1 : 0;
            let id = $(this).val();
            $.ajax({
                url: "{{ route('jobSeeker-status-update-by-admin') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    mode: value,
                    id: id,
                },
                success: function(response) {
                    if (response.is_publish == 1) {
                        toastr.success("job-seeker active success");
                    } else {
                        toastr.warning("job-seeker inactive success");
                    }
                    setTimeout(location.reload.bind(location), 1000);
                }
            })
        })
    </script>
@endpush
