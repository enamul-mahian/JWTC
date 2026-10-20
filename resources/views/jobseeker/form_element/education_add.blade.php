<div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"> {{ __('messages.Addneweducation') }}</h5>
        </div>
        <form action="{{ route('store-education') }}" method="POST" id="form_data">
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Levelofeducation') }} <span
                                    class="text-danger">*</span></label>
                            <select name="degree_level_id" class="form-control" id="degreeLevel" required>
                                <option value="">{{ __('messages.SelectEducationLevel') }}</option>
                                @foreach ($education_levels as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.DegreeTitle') }} <span
                                    class="text-danger">*</span></label>
                            <select id="degreeName" name="degree_name_id" class="form-control" required>
                                <option value="">{{ __('messages.DegreeTitle') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">{{ __('messages.InstituteName') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="institute_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Result') }}</label>
                            <input type="text" name="result" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.PassingYear') }}</label>
                            <input type="text" name="pass_year" class="form-control">
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
