@extends('dashboard.layout.dasboardMaster')
@section('title', 'Company Size')
@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4 class="mb-0">{{ __('Add Skills') }}</h4>
                        @can('skills.create')
                            <a href="{{ route('skills.create') }}">
                                <button class="btn btn-outline-primary"><i class="fa-solid fa-plus me-2"></i> Create</button>
                            </a>
                        @endcan
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table-scroll">
                                <table id="zero_config" class="table border table-striped table-bordered text-wrap">
                                    <thead>
                                        <!-- start row -->
                                        <tr>
                                            <th>SL</th>
                                            <th>Job Category</th>
                                            <th>Skill</th>
                                            @if (auth()->user()->can('skills.edit') || auth()->user()->can('skills.delete'))
                                                <th>Action</th>
                                            @endif
                                        </tr>
                                        <!-- end row -->
                                    </thead>
                                    <tbody>
                                        @foreach ($skills as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->jobCategory?->name }}</td>
                                                <td>{{ $item->skill_name }}</td>
                                                @if (auth()->user()->can('skills.edit') || auth()->user()->can('skills.delete'))
                                                    <td>
                                                        <div class="d-inline-flex">
                                                            @can('skills.edit')
                                                                <a href="{{ route('skills.edit', $item->id) }}">
                                                                    <button class="btn btn-primary  me-2">
                                                                        <i class="fa-regular fa-pen-to-square"></i>
                                                                    </button>
                                                                </a>
                                                            @endcan
                                                            @can('skills.delete')
                                                                {!! Form::open([
                                                                    'route' => ['skills.destroy', $item->id],
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
