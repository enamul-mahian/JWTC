@extends('dashboard.layout.dasboardMaster')
@section('title', 'Add Social Account')
@section('content')
    <section class="pc-container">
        <div class="pc-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="h4"> @yield('title') </span>
                        </div>
                        <div class="card-body table-border-style">
                            <form action="{{ route('payment-gateway-credential.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 mb-4">
                                        <label for="title">Facebook</label>
                                        <input type="text" name="facebook" value="{{ credential('facebook') }}" class="form-control"
                                            placeholder="facebook link">
                                        @error('facebook')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-4">
                                        <label for="title">Google</label>
                                        <input type="text" step="" name="google" value="{{ credential('google') }}"
                                            class="form-control" placeholder="google">
                                        @error('google')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <label for="title">Youtube</label>
                                        <input type="text" step="" name="youtube" value="{{ credential('youtube') }}"
                                            class="form-control" placeholder="youtube link">
                                        @error('youtube')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <label for="title">Linkedin</label>
                                        <input type="text" step="" name="linkedin" value="{{ credential('linkedin') }}"
                                            class="form-control" placeholder="Linkedin link">
                                        @error('linkedin')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <label for="title">Instagram</label>
                                        <input type="text" step="" name="instagram" value="{{ credential('instagram') }}"
                                            class="form-control" placeholder="Instagram link">
                                        @error('instagram')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <label for="title">Twitter</label>
                                        <input type="text" step="" name="twitter" value="{{ credential('twitter') }}"
                                            class="form-control" placeholder="Twitter link">
                                        @error('twitter')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <label for="title">Android App</label>
                                        <input type="text" step="" name="android_app" value="{{ credential('android_app') }}"
                                            class="form-control" placeholder="Android App link">
                                        @error('android_app')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12 mb-4">
                                        <label for="title">iOS App</label>
                                        <input type="text" step="" name="ios_app" value="{{ credential('ios_app') }}"
                                            class="form-control" placeholder="iOS App link">
                                        @error('ios_app')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <button class="btn btn-info" type="submit">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
