@extends('frontend.layout.frontMaster')
@section('title', 'My Library')
@Section('content')
@include('modal.common')
<style>
.manager-info {
    display: flex;
    justify-content: space-between;
}

.manager-meta {
    display: flex;
    gap: 10px;
}

/* Media query for smaller screens */
@media (max-width: 768px) {
    .manager-info {
        flex-direction: column;
        align-items: center;
    }

    .manager-meta {
        margin-top: 10px; /* Adjust margin for better spacing */
    }
}
</style>
<div class="page-header">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="inner-header">
                    <h3> {{ __('messages.MyLibrary') }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>
    <div id="content">
        <div class="container">
            <div class="row">

                <div class="col-lg-12 col-md-6 col-xs-12">
                    <div class="manager-resumes-item">
                        <div class="manager-content">
                            <div class="manager-info">
                                <div class="manager-name">
                                    <h4>{{ __('messages.CVs') }}</h4>
                                </div>
                                <div class="manager-meta">
                                    <a class="btn btn-info btn_modal" href="{{ route('import-cv') }}"> {{ __('messages.Add') }}</a>
                                    <a class="btn btn-primary" href="{{ route('make-resume') }}">{{ __('messages.CreateCV') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="item-body">
                            
                            <div class="content">
                                <div class="row">
                                    @foreach ($resumes as $cv)
                                    @php 
                                    $file = $cv->file;
                                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                                    @endphp
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <img style="height: 50px; margin-right: 10px;" src="{{ getThumbnailPath($extension) }}" alt="" srcset="">
                                        <span class="mt-2">
                                            <a class="btn btn-info btn-sm mr-2" href="{{ asset('assets/images/resume/'.$cv->file) }}">{{ substr($cv->title, 0, 10) }}</a>
                                            <form method="POST" action="{{ route('uploaded-cv-delete', $cv->id) }}" class="d-inline" onsubmit="return confirm('Delete this CV?');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm mr-2">{{ __('messages.Delete') }}</button></form>
                                        </span>
                                    </div>
                                    
                                    @endforeach
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 col-md-6 col-xs-12">
                    <div class="manager-resumes-item">
                        <div class="manager-content">
                            <div class="manager-info">
                                <div class="manager-name">
                                    <h4>{{ __('messages.Coverletters') }}</h4>
                                </div>
                                <div class="manager-meta">
                                    <a class="btn btn-info" href="{{ route('add-cover-letter') }}"> {{ __('messages.Add') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="item-body">
                            
                            <div class="content">
                                <div class="row">
                                    @foreach ($cover as $letter)
                                    @php 
                                    $file = $letter->cover_letter;
                                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                                    @endphp
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <img style="height: 50px; margin-right: 10px;" src="{{ getThumbnailPath('doc') }}" alt="" srcset="">
                                        
                                        <span class="mt-2">
                                            <a class="btn btn-info btn-sm mr-2" href="{{ route('download-cover-letter') }}">{{ __('messages.DownloadWordFile') }}</a>

                                            <a class="btn btn-info btn-sm mr-2" href="{{ route('download-pdf-cover-letter') }}">{{ __('messages.DownloadPDFFile') }}</a>
                                        </span>
                                    </div>
                                    
                                    @endforeach
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-6 col-xs-12">
                    <div class="manager-resumes-item">
                        <div class="manager-content">
                            <div class="manager-info">
                                <div class="manager-name">
                                    <h4>{{ __('messages.Certificatesanddiplomas') }}</h4>
                                </div>
                                <div class="manager-meta">
                                    <a class="btn btn-info btn_modal" href="{{ route('jobseeker-certificate') }}">{{ __('messages.Add') }}</a>
                                </div>
                            </div>
                        </div>
                        <div class="item-body">
                            
                            <div class="content">
                                <div class="row">
                                    @foreach ($certificates as $certificate)
                                    @php 
                                    $file = $certificate->file;
                                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                                    @endphp
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <img style="height: 50px; margin-right: 10px;" src="{{ getThumbnailPath($extension) }}" alt="" srcset="">
                                        <span class="mt-2">
                                            <a class="btn btn-info btn-sm mr-2" href="{{ asset('assets/images/certificate/'.$certificate->file) }}">{{ substr($certificate->title, 0, 10) }}</a>
                                            <form method="POST" action="{{ route('delete-jobseeer-certificate', $certificate->id) }}" class="d-inline" onsubmit="return confirm('Delete this certificate?');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm mr-2">{{ __('messages.Delete') }}</button></form>
                                        </span>
                                    </div>
                                    
                                    @endforeach
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-12 col-md-6 col-xs-12">
                    <div class="manager-resumes-item">
                        <div class="manager-content">
                            <div class="manager-info">
                                <div class="manager-name">
                                    <h4>{{ __('messages.Uploads') }}</h4>
                                </div>
                                <div class="manager-meta">
                                    <a class="btn btn-info btn_modal" href="{{ route('jobseeker-upload') }}">{{ __('messages.Add') }}</a>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="item-body">
                            
                            <div class="content">
                                <div class="row">
                                    @foreach ($uploads as $doc)
                                    @php 
                                    $file = $doc->file;
                                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                                    @endphp
                                    
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <img style="height: 50px; margin-right: 10px;" src="{{ getThumbnailPath($extension) }}" alt="" srcset="">
                                        <span class="mt-2">
                                            <a class="btn btn-info btn-sm mr-2" href="{{ asset('assets/images/document/'.$doc->file) }}">{{ substr($doc->title, 0, 10) }}</a>
                                            <form method="POST" action="{{ route('delete-upload-document', $doc->id) }}" class="d-inline" onsubmit="return confirm('Delete this document?');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm mr-2">{{ __('messages.Delete') }}</button></form>
                                        </span>
                                    </div>
                                    
                                    @endforeach
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
@push('script')
<script src="{{ asset('frontend/assets/script/library.js') }}"></script>
@endpush