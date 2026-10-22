@extends('dashboard.layout.dasboardMaster')
@section('title', 'Change Password')
@section('content')

<section class="section dashboard">
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @if (session('otp_sent'))
        <div class="alert alert-info" role="status">{{ session('otp_sent') }}</div>
    @endif
    @if (config('security.password_change_otp.enabled'))
        <p>Your current password and an email verification code are required to change your password.</p>
        <form action="{{ route('account.password.verify_otp') }}" method="POST" class="card mb-3">
            @csrf
            <div class="card-body">
                <label for="password_change_otp">Email verification code</label>
                <input id="password_change_otp" name="otp" type="text"
                    inputmode="numeric" autocomplete="one-time-code"
                    pattern="[0-9]{6}" minlength="6" maxlength="6" required class="form-control">
                <button type="submit" class="btn btn-primary mt-2">Verify code and change password</button>
            </div>
        </form>
    @endif
    <form action="{{ route('update-admin-password') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" autocomplete="current-password" required>
                        </div>

                        <div class="form-group mb-2">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>

                        <div class="form-group mb-2">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required>
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
