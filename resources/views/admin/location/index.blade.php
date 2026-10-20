@extends('dashboard.layout.dasboardMaster')
@section('title', 'location')
@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="mb-0">{{ __('Location') }}</h4>
                        @can('location.create')
                            <a href="{{ route('location.create') }}">
                                <button class="btn btn-outline-primary"><i class="fa-solid fa-plus me-2"></i> Create</button>
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
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        @if (auth()->user()->can('location.edit') ||
                                                auth()->user()->can('location.delete'))
                                            <th>Action</th>
                                        @endif
                                    </tr>
                                    <!-- end row -->
                                </thead>
                                <tbody>
                                    @foreach ($locations as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->description }}</td>
                                            <td>{!! badgeData($item->status) !!}</td>
                                            
                                            @if (auth()->user()->can('location.edit') ||
                                                    auth()->user()->can('location.delete'))
                                                <td>
                                                    <div class="d-inline-flex">
                                                        @can('location.edit')
                                                            <a href="{{ route('location.edit', $item->id) }}">
                                                                <button class="btn btn-primary me-2">
                                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                                </button>
                                                            </a>
                                                        @endcan
                                                        @can('location.delete')
                                                            {!! Form::open([
                                                                'route' => ['location.destroy', $item->id],
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


@push('script')
    <script>
         $(document).on('click', '.delete-btn', function() {
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
