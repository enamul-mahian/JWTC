
@extends('dashboard.layout.dasboardMaster')
@section('title', 'Job Applicants List')

@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5> @yield('title') </h5>
                        @can('active-job')
                            <a href="{{ route('admin.application-statuses.index') }}" class="btn btn-outline-primary btn-sm">Configure workflow stages</a>
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
                                            <th>Category</th>
                                            <th>Title</th>
                                            <th>Applicant</th>
                                            <th>Status</th>
                                            @can('active-job')
                                                <th>Action</th>
                                            @endcan
                                        </tr>
                                        <!-- end row -->
                                    </thead>
                                    <tbody>
                                        @foreach ($applications as $item)
                                            <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->job?->job_category?->name }}</td>
                                            <td>{{ $item->job?->title }}</td>
                                            <td>{{ $item->user?->name }}</td>
                                            @php $status = getJobApplicationStatus((int) $item->status); @endphp
                                            <td>
                                                @if ($status)
                                                    {{ $status['name'] }} — {{ $status['progress'] }}%
                                                @else
                                                    Unknown status
                                                @endif
                                        </td>
                                            @can('active-job')<!---->
                                                <td>
                                                    <div class="d-inline-flex">
                                                        <button class="btn btn-primary" data-toggle="modal" data-target="#setCodeModal" data-id="{{ $item->id }}">
                                                            Update Status
                                                        </button>
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


    {{-- Modal for Job status update --}}
    <div class="modal fade" id="setCodeModal" tabindex="-1" role="dialog" aria-labelledby="setCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="setCodeForm" method="POST" action="{{ route('jobapplication.update_status') }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="modal-body">
                        <input type="hidden" name="application_id" id="applicationId">
                        @php $stats = getJobApplicationStatuses(); @endphp
                        <div class="form-group">
                            <label for="code">Status</label>
                            <select name="status" id="status" class="form-control">
                                @foreach($stats as $stat)
                                    @if($stat['is_active'])
                                        <option value="{{ $stat['id'] }}">{{ $stat['name'] }} ({{ $stat['progress'] }}%)</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status-note">Update note (visible to applicant)</label>
                            <textarea id="status-note" name="note" maxlength="1000" class="form-control" rows="3"
                                placeholder="Optional reason or next step"></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    // JavaScript to handle modal and form population
    $('#setCodeModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('id'); // Extract info from data-* attributes

        var modal = $(this);
        modal.find('#applicationId').val(id);
    });
</script>
@endpush
