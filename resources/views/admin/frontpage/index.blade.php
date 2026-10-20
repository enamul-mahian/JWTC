@extends('dashboard.layout.dasboardMaster')
@section('title', 'Pages')
@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <a href="{{ route('pages.create') }}" class="btn btn-info">Create New Page </a>
                    </div>
                    <div class="card-body">
                        <div class="table-scroll">
                            <table id="zero_config" class="table border table-striped table-bordered text-nowrap">
                                <thead>
                                    <!-- start row -->
                                    <tr>
                                        <th>SL</th>
                                        <th>Name</th>
                                        <th>URL</th>
                                        <th>Order By</th>
                                        @if (auth()->user()->can('page.edit') ||
                                                auth()->user()->can('page.delete'))
                                            <th>Action</th>
                                        @endif
                                    </tr>
                                    <!-- end row -->
                                </thead>
                                <tbody>
                                    @forelse ($pages as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->url }}</td>
                                            <td>{{ $item->order_by }}</td>
                                            @if (auth()->user()->can('page.edit') ||
                                                    auth()->user()->can('page.delete'))
                                                <td>
                                                    <div class="d-inline-flex justify-content-center align-item-middle">
                                                        @can('page.edit')
                                                            <a href="{{ route('pages.edit', $item->id) }}">
                                                                <button class="btn btn-primary me-2">
                                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                                </button>
                                                            </a>
                                                        @endcan

                                                        @can('page.delete')
                                                        <form method="POST" class="delete-form" action="{{ route('pages.destroy', $item->id) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger delete-btn" onclick="confirmDelete(event)"><i class="fa-solid fa-trash"></i></button>
                                                        </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                    @endforelse
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
        function confirmDelete(event) {
            event.preventDefault(); // Prevent form submission
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
                    event.target.closest('.delete-form').submit();
                }
            });
        }
    </script>
@endpush

