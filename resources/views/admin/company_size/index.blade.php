@extends('dashboard.layout.dasboardMaster')
@section('title', 'Company Size')

@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="mb-0">{{ __('Company Size') }}</h4>
                        @can('company-size.create')
                            <a href="{{ route('company-size.create') }}">
                                <button class="btn btn-outline-primary"><i class="fa-solid fa-plus me-2"></i> Create</button>
                            </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table-scroll">
                                <table id="zero_config" class="table border table-striped table-bordered">
                                    <thead>
                                        <!-- start row -->
                                        <th>SL</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        @if (auth()->user()->can('company-size.edit') || auth()->user()->can('company-size.delete'))
                                            <th>Action</th>
                                        @endif
                                        <!-- end row -->
                                    </thead>
                                    <tbody>
                                        @foreach ($companySizes as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->title }}</td>
                                                <td>{{ $item->status == 1 ? 'Active' : 'Inactive' }}</td>
                                                @if (auth()->user()->can('company-size.edit') || auth()->user()->can('company-size.delete'))
                                                    <td>
                                                        <div class="d-inline-flex">
                                                            @can('company-size.edit')
                                                                <a href="{{ route('company-size.edit', $item->id) }}">
                                                                    <button class="btn btn-primary  me-2">
                                                                        <i class="fa-regular fa-pen-to-square"></i>
                                                                    </button>
                                                                </a>
                                                            @endcan
                                                            @can('company-size.delete')
                                                                {!! Form::open([
                                                                    'route' => ['company-size.destroy', $item->id],
                                                                    'method' => 'delete',
                                                                    'id' => 'delete_form_' . $item->id,
                                                                ]) !!}
                                                                {!! Form::button('<i class="fa-solid fa-trash"></i>', [
                                                                    'class' => 'btn  btn-danger delete-btn',
                                                                    'data-id' => $item->id,
                                                                ]) !!}
                                                                {!! Form::close() !!}
                                                            @endcan
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
    </script>
@endpush
