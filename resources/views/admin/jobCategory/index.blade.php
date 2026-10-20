@extends('dashboard.layout.dasboardMaster')
@section('title', 'Job Categories')
@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="mb-0">{{ __('Job Categories') }}</h4>
                        @can('job-categories.create')
                            <a href="{{ route('job-categories.create') }}">
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
                                            <th>Status</th>
                                            @if (auth()->user()->can('job-categories.edit') || auth()->user()->can('job-categories.delete'))
                                                <th>Action</th>
                                            @endif
                                        </tr>
                                        <!-- end row -->
                                    </thead>
                                    <tbody>
                                        @foreach ($jobCategories as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>
                                                    @if ($item->status == App\Enum\StatusType::ACTIVE)
                                                        <span class="badge bg-primary">Published</span>
                                                    @else
                                                        <span class="badge bg-danger">Unpublished</span>
                                                    @endif
                                                </td>
                                                @if (auth()->user()->can('job-categories.edit') || auth()->user()->can('job-categories.delete'))
                                                    <td>
                                                        <div class="d-inline-flex">
                                                            @can('job-categories.edit')
                                                                <a href="{{ route('job-categories.edit', $item->id) }}">
                                                                    <button class="btn btn-primary me-2">
                                                                        <i class="fa-regular fa-pen-to-square"></i>
                                                                    </button>
                                                                </a>
                                                            @endcan
                                                            @can('job-categories.delete')
                                                                {!! Form::open([
                                                                    'route' => ['job-categories.destroy', $item->id],
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
