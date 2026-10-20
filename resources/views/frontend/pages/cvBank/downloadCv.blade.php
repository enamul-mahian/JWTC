<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"> {{ __('messages.DownloadResumes') }}</h5>
        </div>
        <div class="modal-body">
            <strong>{{ $count }} {{ __('messages.CVsFound') }}</strong>
            <form action="{{ route('download-pdfs') }}" method="POST" id="resumeBank">
                @csrf 
                <div class="input-group mb-3">
                    <input type="text"  name="cv_count" class="form-control" placeholder="{{ __('messages.eg200') }}" required>
                    <input type="hidden" name="category_id" value="{{ $id }}" class="form-control">
                    <div class="input-group-prepend">
                        <button class="btn btn-success text-white" id="basic-addon1" type="submit">{{ __('messages.Download') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
