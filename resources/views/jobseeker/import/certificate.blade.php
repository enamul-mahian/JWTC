<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="alerts-title" id="exampleModalLabel">{{ __('messages.Uploadcertificate') }}</h5>
        </div>
        <form action="{{ route('store-jobseeer-certificate') }}" method="POST" id="form_data" enctype="multipart/form-data">
        @csrf 
            <div class="modal-body">
                <input type="file" name="certificate" class="form-control">
                <small>{{ __('messages.Acceptedformats') }}</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">{{ __('messages.Close') }}</button>
                <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.Save') }}</button>
            </div>
        </form>
    </div>
</div>