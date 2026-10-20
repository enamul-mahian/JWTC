@extends('dashboard.layout.dasboardMaster')
@section('title', 'Degree Level')
@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="mb-0">{{ __('Degree Level') }}</h4>
                        @can('degree-level.create')
                            <a href="{{ route('degree-level.create') }}">
                                <button class="btn btn-outline-primary">
                                    <i class="fa-solid fa-plus me-2"></i> Create
                                </button>
                            </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table-scroll">
                                <table id="zero_config" class="table border table-striped table-bordered text-nowrap">
                                    <thead>
                                        <!-- start row -->
                                        <tr>
                                            <th>SL</th>
                                            <th>Degree Level</th>
                                            <th>Status</th>
                                            @if (auth()->user()->can('degree-level.edit') || auth()->user()->can('degree-level.delete'))
                                                <th>Action</th>
                                            @endif
                                        </tr>
                                        <!-- end row -->
                                    </thead>
                                    <tbody>
                                        @foreach ($degree as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>

                                                <td>
                                                    @if ($item->status == 1)
                                                        <span class="badge bg-primary">Published</span>
                                                    @else
                                                        <span class="badge bg-danger">Unpublished</span>
                                                    @endif
                                                </td>
                                                @if (auth()->user()->can('degree-level.edit') || auth()->user()->can('degree-level.delete'))
                                                    <td>
                                                        <div class="d-inline-flex">
                                                            @can('degree-level.edit')
                                                                <a href="{{ route('degree-level.edit', $item->id) }}">
                                                                    <button class="btn btn-primary me-2">
                                                                        <i class="fa-regular fa-pen-to-square"></i>
                                                                    </button>
                                                                </a>
                                                            @endcan
                                                            @can('degree-level.delete')
                                                                {!! Form::open([
                                                                    'route' => ['degree-level.destroy', $item->id],
                                                                    'method' => 'delete',
                                                                    'id' => 'delete_form_' . $item->id,
                                                                ]) !!}
                                                                {!! Form::button('<i class="fa-solid fa-trash"></i>', [
                                                                    'class' => 'btn btn-danger delete-btn',
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
