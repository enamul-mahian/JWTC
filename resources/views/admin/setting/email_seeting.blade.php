@extends('dashboard.layout.dasboardMaster')
@section('title', 'Email Setting')
@section('content')

<section class="section dashboard">
    <form action="{{ route('email-setting-update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3 mt-5">
                            <label for="inputText" class="col-sm-2 col-form-label">Mail Host <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" placeholder="Mail Host" class="form-control" required
                                    value="{{ $general->mail_host }}" name="mail_host">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="inputText" class="col-sm-2 col-form-label">Mail Port <span
                                    class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" required name="mail_port" placeholder="Mail Port"
                                    value="{{ $general->mail_port }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="inputPassword" class="col-sm-2 col-form-label">Mail Username </label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" required name="mail_username"
                                    value="{{ $general->mail_username }}" placeholder="Mail Username">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="inputPassword" class="col-sm-2 col-form-label">Mail Password </label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" required name="mail_password"
                                    value="{{ $general->mail_password }}" placeholder="Mail Password">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputPassword" class="col-sm-2 col-form-label">Mail From Name </label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" required name="mail_from_name"
                                    value="{{ $general->mail_from_name }}" placeholder="Mail Form Name">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputPassword" class="col-sm-2 col-form-label">Mail From Email Address </label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" required name="mail_from_address"
                                    value="{{ $general->mail_from_address }}" placeholder="Mail From Email Address">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="inputPassword" class="col-sm-2 col-form-label">Mail Encryption </label>
                            <div class="col-sm-10">
                                <select class="form-select form-control" style="width: 100%; height: auto;"
                                    name="mail_encryption">
                                    <option {{ $general->mail_encryption == "tls" ? 'selected' : '' }} value="tls">TLS
                                    </option>
                                    <option {{ $general->mail_encryption == "ssl" ? 'selected' : '' }} value="ssl">SSL
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="inputPassword" class="col-sm-2 col-form-label">Mail Status </label>
                            <div class="col-sm-10">
                                <select class="form-select form-control" style="width: 100%; height: auto;"
                                    name="mail_status">
                                    <option {{ $general->mail_status == "Active" ? 'selected' : '' }} value="Active">
                                        Active</option>
                                    <option {{ $general->mail_status == "Inactive" ? 'selected' : '' }}
                                        value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3 justify-content-end">
                            <div class="col-auto">
                                <a href="{{ route('send-test-email') }}" class="btn btn-info me-2">Send Test EMail</a>
                            </div>
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

@push('custom-script')
<script>
@foreach($errors->all() as $error)
toastr.error("{{ $error }}");
@endforeach
</script>
@endpush