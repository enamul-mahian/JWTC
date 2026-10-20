@extends('frontend.layout.frontMaster')
@Section('title', 'Jobseeker Dashbaord')
@Section('content')

<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="inner-header">
                    <h3>{{ __('messages.EmailResume') }}</h3>
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
                    <h4>{{ __('messages.Profile') }}</h4>
                    @include('jobseeker.partials.sidenav')
                </div>
            </div>
            <div class="col-lg-8 col-md-8 col-xs-12">
                <div class="card">
                    <div class="card-body">
                        <form id="emailForm" action="{{ route('send-resume') }}" method="post" enctype="multipart/form-data">
                            @csrf 
                            <div class="form-group">
                                <label for="txtUserEmail">{{ __('messages.MyEmail') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">
                                            <i class="lni lni-envelope"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control from-control-emailresume" placeholder="{{ __('messages.EmailAddressinput') }}" name="txtUserEmail" value="{{ auth()->user()->name }} <{{auth()->user()->email}}>">
                                </div>
                            </div>
                        
                            <div class="form-group">
                                <label for="txtCompanyEmail">{{ __('messages.CompanyEmail') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon2">
                                            <i class="lni lni-envelope"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="{{ __('messages.CompanyEmailAddress') }}" name="txtCompanyEmail">
                                </div>
                            </div>
                        
                            <div class="form-group">
                                <label for="txtMailSubject">{{ __('messages.Subject') }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon3">
                                            <i class="lni lni-book"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control from-control-emailresume" aria-label="Subject" placeholder="{{ __('messages.TypeaSubject') }}" name="txtMailSubject">
                                </div>
                            </div>
        
                            <div class="form-group">
                                <label for="txtApplication">{{ __('messages.Message') }}</label>
                                <textarea class="form-control" aria-label="Message" name="txtApplication" rows="3" placeholder="{{ __('messages.YourMessage') }}..."></textarea>
                            </div>
                            <div class="form-group">
                                <label for="txtApplication">{{ __('messages.Attachment') }} <span class="text-danger">(File Type: pdf, doc, docx. Max:2048 KB)</span></label>
                                <input class="form-control" id="imageInput" name="attachment" type="file"></input>
                            </div>

                            <button type="submit" id="sendEmailBtn" class="btn btn-sm btn-info">{{ __('messages.SendCV') }}</button>
                        </form>
                    </div>
                </div>                
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function () {
        $('#sendEmailBtn').click(function (e) {
            e.preventDefault();
            $(this).prop('disabled', true);
            $('#sendEmailBtn').text('please wait...');
            var formData = new FormData($('#emailForm')[0]);
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });
            $.ajax({
                type: "POST",
                url: "{{ route('send-resume') }}",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response.errors) {
                        $.each(response.errors, function (key, value) {
                            toastr.error(value[0]);
                        });
                        $('#sendEmailBtn').prop('disabled', false);
                        $('#sendEmailBtn').text('Send CV');
                    } else {
                        toastr.success(response.success);
                        $('#sendEmailBtn').prop('disabled', false);
                        $('#sendEmailBtn').text('Send CV');
                    }
                },
                error: function (error) {
                    toastr.error('Something went wrong');
                    $('#sendEmailBtn').prop('disabled', false);
                    $('#sendEmailBtn').text('Send CV');
                },
            });
        });
    });
</script>
@endpush
