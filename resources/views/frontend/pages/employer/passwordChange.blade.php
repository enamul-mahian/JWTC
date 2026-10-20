@extends('frontend.layout.frontMaster')
@section('title', 'Change Password')
@section('content')
    <!-- Page Header Start -->
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
    <!-- Page Header End -->
    <!-- Start Content -->
    <div id="content">
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="right-sideabr">
                        @include('frontend.pages.employer.sideMenu')
                    </div>
                </div>
                <div class="col-md-8 col-sm-8 col-xs-12">
                    <div class="job-alerts-item">
                        <h3 class="alerts-title">{{ __('messages.ChangePassword') }}</h3>
                        @if (Session::has('message'))
                            <div class="alert alert-{{ session::get('class') }} alert-dismissible fade show" role="alert">
                                {{ session::get('message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <form action="{{ route('update.pass') }}" method="post">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <div class="form-group is-empty">
                                <label class="control-label">{{ __('messages.OldPassword') }}<span class="text-danger">*</span> </label>
                                <input class="form-control" name="old_pass" type="password">
                                <span class="material-input"></span>
                            </div>
                            <div class="form-group is-empty">
                                <label class="control-label">{{ __('messages.NewPassword') }}<span class="text-danger">*</span></label>
                                <input class="form-control" name="new_pass" type="password">
                                <span class="material-input"></span>
                            </div>
                            <button class="btn btn-common" type="submit"> {{ __('messages.SaveChange') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Content -->
@endsection
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous">
    </script>
@endpush
