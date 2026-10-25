@extends('dashboard.layout.dasboardMaster')
@section('title', 'Add User')

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
                        <form action="{{ route('user.update', $user->id) }}" method="post" id="admin-user-edit-form">
                            @method('put')
                            @csrf
                            <div class=" mb-3">
                                <label for="name">Name <span class="text-danger">*</span> </label>
                                <input type="text" name="name" placeholder="enter name" value="{{ $user->name }}" class="form-control" required>
                            </div>

                            <div class=" mb-3">
                                <label for="email"> Email  <span class="text-danger">*</span> </label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" placeholder="enter email">
                            </div>

                            <div class=" mb-3">
                                <label for="email"> Phone (optional) </label>
                                <input type="tel" id="numberInput" name="phone" class="form-control" min="0" value="{{ $user->phone }}" placeholder="enter phone">
                            </div>

                            <div class=" mb-3">
                                <label for="email"> Role  <span class="text-danger">*</span> </label>
                                <select name="role" id="" class="form-select">
                                    <option value="">Select role</option>
                                    @foreach ($role as $value)
                                        <option {{ in_array($value->name,  $roles->toArray() ) ? 'selected' : '' }} value="{{ $value->name }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button class="btn btn-primary me-2" type="submit" id="admin-user-save">Save changes</button>
                            <a href="{{ route('user-management.index') }}">
                                <button class="btn btn-danger" type="button"> Back </button>
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('script')
<script>
    // Prevent accidental rapid repeat submissions that can trigger hosting
    // rate-limit rules. All authorization and validation remain server-side.
    document.getElementById('admin-user-edit-form')?.addEventListener('submit', function () {
        const submit = document.getElementById('admin-user-save');
        if (submit) {
            submit.disabled = true;
            submit.textContent = 'Saving…';
        }
    });
</script>
@endpush
