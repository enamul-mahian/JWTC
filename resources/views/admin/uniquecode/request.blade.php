
@extends('dashboard.layout.dasboardMaster')
@section('title', 'Code Request List')

@section('content')
    <section class="datatables">
        <!-- basic table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5> @yield('title') </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="table-scroll">
                                <table id="zero_config" class="table border table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>User</th>
                                            <th>Date</th>
                                            <th>Code</th>
                                            <th>Is Used</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($datas as $data)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $data->user->name ?? 'N/A' }}</td>
                                                <td>{{ $data->date }}</td>
                                                <td>{{ $data->code }}</td>
                                                <td>{{ $data->is_used ? 'Yes' : 'No' }}</td>
                                                <td>
                                                    <button class="btn btn-primary" data-toggle="modal" data-target="#setCodeModal" data-id="{{ $data->id }}" data-user="{{ $data->user->name ?? 'N/A' }}">
                                                        Set Code
                                                    </button>
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
        </div>
    </section>

    {{-- Modal for setting code --}}
    <div class="modal fade" id="setCodeModal" tabindex="-1" role="dialog" aria-labelledby="setCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="setCodeForm" method="POST" action="{{ route('admin.unique_codes.update_code') }}">
                    @csrf
                    @method('PATCH')
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="setCodeModalLabel">Set Code for <span id="user-name"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    
                    <div class="modal-body">
                        <input type="hidden" name="unique_code_id" id="uniqueCodeId">
                        <div class="form-group">
                            <label for="code">Code</label>
                            <input type="text" class="form-control" name="code" id="code" required>
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
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
    // JavaScript to handle modal and form population
    $('#setCodeModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('id'); // Extract info from data-* attributes
        var userName = button.data('user'); // Extract user name

        var modal = $(this);
        modal.find('#uniqueCodeId').val(id);
        modal.find('#user-name').text(userName);
    });
</script>
@endpush
