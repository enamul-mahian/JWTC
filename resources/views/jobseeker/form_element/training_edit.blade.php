<div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"> {{ __('messages.UpdateTraining') }}</h5>
        </div>
        <form action="{{ route('update-training', $training->id) }}" method="POST" id="form_data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Country') }} <span
                                    class="text-danger">*</span></label>
                            <select name="country" class="form-control" required>
                                @foreach ($countries as $country)
                                    <option {{ $country->name == $training->country ? 'selected' : '' }}
                                        value="{{ $country->name }}">{{ $country->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for=""> {{ __('messages.TrainingTitle') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                value="{{ $training->title ?? null }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.TopicsCovered') }}</label>
                            <input type="text" name="topic" class="form-control"
                                value="{{ $training->topic ?? null }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.TrainingYear') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="training_year" class="form-control"
                                value="{{ $training->training_year ?? null }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"> {{ __('messages.Institute') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="training_institute" class="form-control"
                                value="{{ $training->training_institute ?? null }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label"> {{ __('messages.Duration') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="training_duration" class="form-control"
                                value="{{ $training->training_duration ?? null }}" required>
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
