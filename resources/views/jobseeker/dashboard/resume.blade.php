@extends('frontend.layout.frontMaster')
@section('title', 'Create CV')
@Section('content')
<style>
.iframe-container {
    width: 794px;
    height: 100%;
}

iframe {
    width: 100%;
    height: 100vh;
    border: none;
}
</style>
<div class="page-header">
<div class="container">
    <div class="row">         
    <div class="col-lg-12">
        <div class="inner-header">
            <h3>{{ __('messages.CreateResume') }}</h3>
        </div>
    </div>
    </div>
</div>
</div>

<section id="content">
    <div class="container">
      <div class="row justify-content-center">
        <div class="iframe-container" id="myContent">
            <iframe id="myIframe" src="{{ route('get-resume-template') }}" frameborder="0"></iframe>
        </div>
        <div class="col-md-12">
            <a class="btn btn-info pull-right"  href="{{ route('jobseeker-generate-pdf') }}" id="generatePdf"> {{ __('messages.CreateCVNow') }}</a>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('script')
<script src="{{ asset('frontend/assets/script/job_seeker.js') }}"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.4/jspdf.min.js"></script> --}}
<link rel="stylesheet" href="https://printjs-4de6.kxcdn.com/print.min.css">
<script src="https://printjs-4de6.kxcdn.com/print.min.js"></script>
<script>
    function printPDF() {
        // Define options for Print.js
        var options = {
            printable: 'myContent',
            type: 'html',
            header: 'Print.js PDF',
            targetStyles: ['*'], // Apply all styles
            documentTitle: 'Print.js PDF',
            showModal: true,
            modalMessage: 'Generating PDF...',
            frameId: 'printFrame', // Specify an iframe for PDF rendering
            onPrintDialogClose: function() {
                // This callback is triggered when the print dialog is closed
                // You can add additional logic here if needed
            }
        };

        // Trigger Print.js to print and generate a PDF
        printJS(options);
    }
</script>

@endpush