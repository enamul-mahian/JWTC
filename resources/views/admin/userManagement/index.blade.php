@extends('dashboard.layout.dasboardMaster')
@section('title', 'User List')

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
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>User Type</th>
                                            <th>Phone</th>
                                            @can('user.edit')
                                                <th>Action</th>
                                            @endcan
                                        </tr>
                                        <!-- end row -->
                                    </thead>
                                    <tbody>
                                        @foreach ($user as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td class="w-75">{{ $item->email }}</td>
                                                <td class="w-75">{{ $item->user_type }}</td>
                                                <td class="w-75">{{ $item->phone }}</td>
                                                @can('user.edit')
                                                    <td class="w-75">
                                                        @if ($item->user_type != App\Enum\RoleType::ADMIN)
                                                            <a href="{{ route('user.edit', $item->id) }}">
                                                                <button class="btn btn-primary">
                                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                                </button>
                                                            </a>
                                                        @endif
                                                        @if(in_array(strtolower((string) $item->user_type), ['employer','job_seeker','staff'], true))
                                                            <a class="btn btn-outline-secondary ms-1" href="{{ route('admin.users.actions', $item->id) }}" aria-label="Manage account for {{ $item->name }}">Password / Delete</a>
                                                        @endif
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
