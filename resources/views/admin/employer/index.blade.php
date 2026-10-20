@extends('dashboard.layout.dasboardMaster')
@section('title', 'Employer List')
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
                        <h5> @yield('title') </h5>
                        @can('user.create')
                            <a href="{{ route('user.create') }}">
                                <button class="btn btn-outline-primary"> Create New User </button>
                            </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table-scroll">
                                <table id="zero_config" class="table border table-striped table-bordered">
                                    <thead>
                                        <!-- start row -->
                                        <tr>
                                            <th>SL</th>
                                            <th>Company Name</th>
                                            <th>ESTD</th>
                                            <th>Size</th>
                                            <th>Industry</th>
                                            <th>Status</th>
                                        </tr>
                                        <!-- end row -->
                                    </thead>
                                    <tbody>
                                        @foreach ($employers as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item?->company_name }}</td>
                                                <td>{{ $item?->estd_year }}</td>
                                                <td>{{ $item?->company_sizes?->title }}</td>
                                                <td>{{ $item?->industryType?->name }}</td>
                                                <td>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" value="{{ $item->id }}"
                                                            type="checkbox" name="status" <?php echo $item->status == 1 ? 'checked' : ''; ?>
                                                            role="switch" id="flexSwitchCheckDefault">
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
@push('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.10.2/sweetalert2.min.js"
        integrity="sha512-k1jHgQwcMNMFymeyYv93tJOsIGpceFgh5VDTq2B5pF0pICXBzRGY97vlMobgYA4azK7936iOWkqm/C1vv/PKMA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $('.delete-btn').on('click', function() {
            let id = $(this).data('id')

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#delete_form_' + id).submit()
                }
            });
        })

        $("input[name=status]").on('change', function() {
            let value = $(this).prop('checked') == true ? 1 : 0;
            let id = $(this).val();
            $.ajax({
                url: "{{ route('employer.update') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    mode: value,
                    id: id,
                },
                success: function(response) {
                    toastr.success("Employer status updated success");
                }
            })
        })
    </script>
@endpush
