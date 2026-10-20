@extends('dashboard.layout.dasboardMaster')
@section('title', 'Roles List')

<style>
    .permission{
        width: 550px !important;
    }
</style>
@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        @yield('title')
                    </div>
                    <div class="card-body">
                        <div class="table-scroll">
                            <table  class="table border table-striped table-bordered">
                                <thead>
                                    <!-- start row -->
                                    <tr>
                                        <th>SL</th>
                                        <th>Name</th>
                                        <th class="permission">Permission</th>
                                        <th>Action</th>
                                    </tr>
                                    <!-- end row -->
                                </thead>
                                <tbody>
                                    @foreach ($role as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>
                                                @foreach ($item->permissions as $permission)
                                                    <button
                                                        class="btn btn-sm btn-info mt-2">{{ $permission->name }}
                                                    </button>
                                                @endforeach

                                            </td>
                                            <td>
                                                <div class="d-inline-flex">
                                                    {{-- @can('role edit') --}}
                                                    <a href="{{ route('role.edit', $item->id) }}">
                                                        <button class="btn btn-primary btn-sm me-2">
                                                            <i class="fa-regular fa-pen-to-square"></i>
                                                        </button>
                                                    </a>
                                                    {{-- @endcan --}}
                                                    {{-- @can('role delete') --}}
                                                    {!! Form::open([
                                                        'route' => ['role.destroy', $item->id],
                                                        'method' => 'delete',
                                                        'id' => 'delete_form_' . $item->id,
                                                    ]) !!}
                                                    {!! Form::button('<i class="fa-solid fa-trash"></i>', [
                                                        'class' => 'btn btn-sm btn-danger delete-btn',
                                                        'data-id' => $item->id,
                                                    ]) !!}
                                                    {!! Form::close() !!}
                                                    {{-- @endcan --}}

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
