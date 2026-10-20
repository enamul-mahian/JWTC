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
                        <form action="{{ route('user.store') }}" method="post">
                            @csrf
                            <div class=" mb-3">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" placeholder="enter name" class="form-control" value="{{ old('name') }}" required>
                            </div>

                            <div class=" mb-3">
                                <label for="email"> Email  <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="enter email">
                            </div>

                            <div class=" mb-3">
                                <label for="email"> Phone  <span class="text-danger">*</span></label>
                                <input type="number" id="numberInput" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="enter phone">
                            </div>

                            <div class=" mb-3">
                                <label for="email"> Password  <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" placeholder="enter password">
                            </div>

                            <div class=" mb-3">
                                <label for="email"> Role  <span class="text-danger">*</span></label>
                                <select name="role" id="" class="form-select">
                                    @foreach ($role as $value)
                                        <option value="{{ $value->name }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button class="btn btn-primary me-2" type="submit">Submit</button>
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
document.getElementById("numberInput").addEventListener("input", function() {
    if (this.value < 0) {
        this.value = 0;
    }
});
</script>
@endpush