@extends('frontend.layout.frontMaster')
@section('title', 'Verify your email')
@section('content')
<section class="section-padding mt-5">
    <div class="container" style="max-width: 560px;">
        <div class="page-login-form box">
            <h2>Verify your email</h2>
            <p>We sent a six-digit code to your registration email. It expires in 10 minutes.</p>
            <p class="text-muted">Didn't get the email? Wait one minute before starting registration again to request a fresh code.</p>
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('registration.otp.confirm') }}">
                @csrf
                <div class="form-group">
                    <label for="registration-otp">Email verification code</label>
                    <input id="registration-otp" type="text" name="otp" inputmode="numeric"
                        autocomplete="one-time-code" minlength="6" maxlength="6" pattern="[0-9]{6}"
                        class="form-control" placeholder="Enter six-digit code" required autofocus>
                </div>
                <button type="submit" class="btn btn-common">Verify and create account</button>
            </form>
        </div>
    </div>
</section>
@endsection
