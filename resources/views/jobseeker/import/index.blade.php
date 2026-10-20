<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"> {{ __('messages.UploadCV') }}</h5>
        </div>
        <form action="{{ route('store-import-cv') }}" method="POST" id="form_data" enctype="multipart/form-data">
        @csrf 
            <div class="modal-body">
                <input type="file" name="resume" class="form-control">
                <small>{{ __('messages.Acceptedformats') }}</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">{{ __('messages.Close') }}</button>
                <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.Save') }}</button>
            </div>
        </form>
    </div>
</div>