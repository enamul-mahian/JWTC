@extends('dashboard.layout.dasboardMaster')
@section('title', 'Getway Credentials')
@section('content')
    <section class="pc-container">
        <div class="pc-content">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span class="h4"> @yield('title')</span>
                        </div>
                        <div class="card-body table-border-style">
                            <form action="{{ route('payment-gateway-credential.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 mb-4">
                                        <label for="title">Client ID <span class="text-danger">*</span></label>
                                        <input type="text" name="client_id" value="{{ credential('client_id') }}"
                                            class="form-control" placeholder="Client ID">
                                        @error('client_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12 mb-4">
                                        <label for="title">Client Secret <span class="text-danger">*</span></label>
                                        <input type="text" step="" name="client_secret"
                                            value="{{ credential('client_secret') }}" class="form-control"
                                            placeholder="client secret">
                                        @error('client_secret')
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
