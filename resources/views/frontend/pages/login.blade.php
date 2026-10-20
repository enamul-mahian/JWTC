@extends('frontend.layout.frontMaster')
@section('title', "$title")

@section('content')
    <!-- Content section Start -->
    <section id="content" class="section-padding mt-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-6 col-xs-12">
                    <div class="page-login-form box">
                        <h3> {{ $title }} </h3>
                        @if (Session::has('message'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">

                                {{ session::get('message') }}

                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        <form class="login-form" action="{{ route('user.login') }}" method="post">
                            @csrf
                            <div class="form-group">
                                <div class="input-icon">
                                    <i class="lni-user"></i>
                                    <input type="text" id="sender-email" class="form-control" name="email"
                                        placeholder="email@example.com">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-icon">
                                    <i class="lni-lock"></i>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="{{ __('messages.Password') }}">
                                </div>
                            </div>
                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="exampleCheck1">
                                <label class="form-check-label"
                                    for="exampleCheck1">{{ __('messages.KeepMeSignedIn') }}</label>
                            </div>
                            <button class="btn btn-common log-btn">{{ __('messages.Submit') }}</button>
                        </form>

                        <ul class="form-links">
                            <li class="d-flex justify-content-between">
                                <span>{{ __('messages.Notregisteryet') }}
                                    <a href="{{ route('registration.form') }}"
                                        class="mr-auto">{{ __('messages.registernow') }}
                                    </a>
                                </span>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        #copyright {
            margin-top: 338px;
        }
    </style>
@endsection
