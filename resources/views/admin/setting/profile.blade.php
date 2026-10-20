@extends('dashboard.layout.dasboardMaster')
@section('title', 'Email Setting')
@section('content')

<section class="section dashboard">
    <form action="{{ route('profile-update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3 mt-5">
                            <label for="inputText" class="col-sm-2 col-form-label">Name <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" placeholder="Mail Host" class="form-control" required
                                    value="{{ $user->name }}" name="name">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="inputText" class="col-sm-2 col-form-label">Email<span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" required name="email" placeholder="Mail Port"
                                    value="{{ $user->email }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="inputPassword" class="col-sm-2 col-form-label">Profile Picture </label>
                            <div class="col-sm-10">
                                <input type="file" name="photo" class="form-control">
                            </div>
                        </div>


                        <div class="row mb-3 justify-content-end">
                            <div class="col-auto">
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary me-2">Back</a>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</section>

@endsection
