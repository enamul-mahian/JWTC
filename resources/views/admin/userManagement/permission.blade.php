@extends('dashboard.layout.dasboardMaster')
@section('title', 'User Permission')

@section('content')
    <section class="datatables">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="mb-0"> {{ __('Role Lists') }}</h4>
                        <a href="{{ route('role.create') }}">
                            <button class="btn btn-outline-primary">Add Role</button>
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-scroll">
                            <table id="zero_config" class="table border table-striped table-bordered text-wrap">
                                <thead>
                                    <!-- start row -->
                                    <tr>
                                        <th>SL</th>
                                        <th>Role</th>
                                        @if (auth()->user()->can('role.edit') || auth()->user()->can('role.delete'))
                                            <th>Action</th>
                                        @endcan
                                </tr>
                                <!-- end row -->
                            </thead>
                            <tbody>
                                @foreach ($role as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->name }}</td>
                                        @if (auth()->user()->can('role.edit') || auth()->user()->can('role.delete'))
                                            <td>
                                                <div class="d-inline-flex">
                                                    <a href="{{ route('role.show', $item->id) }}">
                                                        <button class="btn btn-info"><i class="fa-solid fa-eye"></i></button>
                                                    </a>
                                                    @if (
                                                        !in_array($item->name, [
                                                            App\Enum\RoleType::ADMIN,
                                                            App\Enum\RoleType::JOB_SEEKER,
                                                            App\Enum\RoleType::EMPLOYER,
                                                            App\Enum\RoleType::SUPER_ADMIN,
                                                        ]))
                                                        <a href="{{ route('role.edit', $item->id) }}">
                                                            <button class="btn btn-outline-warning">
                                                                <i class="fa-regular fa-pen-to-square"></i>
                                                            </button>
                                                        </a>
                                                        {!! Form::open([
                                                            'route' => ['role.destroy', $item->id],
                                                            'method' => 'delete',
                                                            'id' => 'delete_form_' . $item->id,
                                                        ]) !!}
                                                        {!! Form::button('<i class="fa-solid fa-trash"></i>', [
                                                            'class' => 'btn ms-2 btn-outline-danger delete-btn',
                                                            'data-id' => $item->id,
                                                        ]) !!}
                                                        {!! Form::close() !!}
                                                    @endif
                                                </div>
                                            </td>
                                        @endif

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
    $(document).on('click', '.delete-btn', function() {
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

    $('#my_table').dataTable()
</script>
@endpush
