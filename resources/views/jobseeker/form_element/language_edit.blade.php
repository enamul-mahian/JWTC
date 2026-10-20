<div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"> {{ __('messages.Updatelanguage') }}</h5>
        </div>
        <form action="{{ route('update-language', $lang->id) }}" method="POST" id="form_data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Language') }} <span
                                    class="text-danger">*</span></label>
                            <input class="form-control" name="language_name" placeholder="{{ __('messages.English') }}"
                                type="text" value="{{ $lang ? $lang->language_name : null }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">{{ __('messages.Listening') }} </label>
                            <select name="listening_proficiency" class="form-control">
                                <option {{ $lang->listening_proficiency == 'Basic' ? 'selected' : '' }} value="Basic">
                                    {{ __('messages.Basic') }}</option>
                                <option {{ $lang->listening_proficiency == 'Intermediate' ? 'selected' : '' }}
                                    value="Intermediate">{{ __('messages.Intermediate') }}</option>
                                <option {{ $lang->listening_proficiency == 'Advanced' ? 'selected' : '' }}
                                    value="Advanced">{{ __('messages.Advanced') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Reading') }}</label>
                            <select name="reading_proficiency" class="form-control">
                                <option {{ $lang->reading_proficiency == 'Basic' ? 'selected' : '' }} value="Basic">
                                    {{ __('messages.Basic') }}</option>
                                <option {{ $lang->reading_proficiency == 'Intermediate' ? 'selected' : '' }}
                                    value="Intermediate">{{ __('messages.Intermediate') }}</option>
                                <option {{ $lang->reading_proficiency == 'Advanced' ? 'selected' : '' }}
                                    value="Advanced">{{ __('messages.Advanced') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Writting') }}</label>
                            <select name="writting_proficiency" class="form-control">
                                <option {{ $lang->writting_proficiency == 'Basic' ? 'selected' : '' }} value="Basic">
                                    {{ __('messages.Basic') }}</option>
                                <option {{ $lang->writting_proficiency == 'Intermediate' ? 'selected' : '' }}
                                    value="Intermediate">{{ __('messages.Intermediate') }}</option>
                                <option {{ $lang->writting_proficiency == 'Advanced' ? 'selected' : '' }}
                                    value="Advanced">{{ __('messages.Advanced') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm"
                    data-dismiss="modal">{{ __('messages.Close') }}</button>
                <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.Save') }}</button>
            </div>
        </form>
    </div>
</div>
