
@extends('dashboard.layout.dasboardMaster')
@section('title', 'Active Job List')

@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5> @yield('title') </h5>
                        {{-- @can('user.create')
                            <a href="{{ route('user.create') }}">
                                <button class="btn btn-outline-primary"> Create New User </button>
                            </a>
                        @endcan --}}
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table-scroll">
                                <table id="zero_config" class="table border table-striped table-bordered">
                                    <thead>
                                        <!-- start row -->
                                        <tr>
                                            <th>SL</th>
                                            <th>Category</th>
                                            <th>Title</th>
                                            <th>vacancies</th>
                                            <th>Deadline</th>
                                            <th>Job Publised</th>
                                            @can('active-job')
                                                <th>Action</th>
                                            @endcan
                                        </tr>
                                        <!-- end row -->
                                    </thead>
                                    <tbody>
                                        @foreach ($items as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->job_category?->name }}</td>
                                            <td>{{ $item->title }}</td>
                                            <td>{{ $item->vacancies }}</td>
                                            <td>{{ $item->application_deadline }}</td>
                                            <td>
                                                @if ($item->is_publish == 1)
                                                    <span class="badge text-bg-warning"> Published </span>
                                                @else
                                                <span class="badge text-bg-danger">Not Published</span>
                                                @endif
                                                

                                                {{-- <div class="form-check form-switch">
                                                    <input class="form-check-input" value="{{ $item->id }}"
                                                        type="checkbox" name="status" <?php echo $item->is_publish == 1 ? 'checked' : ''; ?> role="switch"
                                                        id="flexSwitchCheckDefault">
                                                </div> --}}
                                            </td>
                                            @can('active-job')
                                                <td>
                                                    <div class="d-inline-flex">
                                                        <a href="{{ route('job.show', $item->id) }}">
                                                            <button class="btn btn-info me-2">
                                                                <i class="fa-regular fa-eye"></i>
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('job.show-apply', $item->id) }}">
                                                            <button class="btn btn-info me-2" title="Applicants">
                                                                <i class="fa-regular fa-user"></i>
                                                            </button>
                                                        </a>
                                                    </div>
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
