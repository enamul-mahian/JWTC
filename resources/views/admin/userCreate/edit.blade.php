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
                        <form action="{{ route('user.update', $user->id) }}" method="post">
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
