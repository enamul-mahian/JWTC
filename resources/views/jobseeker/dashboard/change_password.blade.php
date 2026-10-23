@extends('jobseeker.layout.workspace')
@section('title','Change Password')
@section('workspace_title','Change Password')
@section('workspace_content')<section class="jg-profile-card"><h2>Account security</h2>                        <form method="POST" action="{{ route('jobseeker-password-update') }}" id="password_form">
                            @csrf
    
                            <div class="form-group">
                                <label for="current_password">{{ __('messages.CurrentPassword') }}</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="new_password">{{ __('messages.NewPassword') }}</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                            </div>

                            <div class="form-group">
                                <label for="new_password_confirmation">{{ __('messages.ConfirmNewPassword') }}</label>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required>
                            </div>

    
                            <button type="submit" class="btn btn-info btn-sm" id="change-password-btn">{{ __('messages.ChangePassword') }}</button>
                        </form>
                        @if (config('security.password_change_otp.enabled'))
                            <form method="POST" action="{{ route('account.password.verify_otp') }}" id="password-otp-form" class="mt-4">
                                @csrf
                                <div class="form-group">
                                    <label for="password-otp">Email verification code</label>
                                    <input id="password-otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" minlength="6" autocomplete="one-time-code" class="form-control" placeholder="Enter six-digit code" required>
                                </div>
                                <button class="btn btn-info btn-sm" type="submit">Verify code and change password</button>
                            </form>
                        @endif
</section>@endsection
    @push('script')
    <script>
        $(document).ready(function () {
            $('#password-otp-form').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    headers: {'Accept': 'application/json'},
                    success: function (data) {
                        toastr.success(data.message);
                        window.location.href = '{{ route('login') }}';
                    },
                    error: function (xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Invalid or expired code.';
                        toastr.error(msg);
                    }
                });
            });
        });
        $(document).ready(function () {
            $('#change-password-btn').on('click', function (e) {
                e.preventDefault();
                var formData = $('#password_form').serialize();
                $.ajaxSetup({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                    },
                });
                $.ajax({
                    type: 'POST',
                    url: '/job_seeker/password-update',
                    data: formData,
                    success: function (data) {
                        toastr.success(data.success);
                        $('#password_form')[0].reset();
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON;
                        $.each(errors, function (key, value) {
                            toastr.error(value);
                        });

                        $('#password_form')[0].reset();
                    }
                });
            });
        });
    </script> 
    @endpush
