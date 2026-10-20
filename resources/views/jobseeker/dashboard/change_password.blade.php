@extends('frontend.layout.frontMaster')
@Section('title', 'Jobseeker Dashbaord')
@Section('content')
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="inner-header">
                        <h3>{{ __('messages.ChangePassword') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="content">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-xs-12">
                    <div class="right-sideabr">
                        <h4>{{ __('messages.ChangePassword') }} </h4>
                        @include('jobseeker.partials.sidenav')
                    </div>
                </div>
                <div class="col-lg-8 col-md-8 col-xs-12">
                    <div class="job-alerts-item candidates">
                        <form method="POST" action="{{ route('jobseeker-password-update') }}" id="password_form">
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
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection

    @push('script')
    <script>
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
