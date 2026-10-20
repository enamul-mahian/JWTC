<div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel"> {{ __('messages.UpdateEducation') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('update-education', $education->id) }}" method="POST" id="form_data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Levelofeducation') }} <span
                                    class="text-danger">*</span></label>
                            <select name="degree_level_id" class="form-control" id="degreeLevel" required>
                                @foreach ($education_levels as $item)
                                    <option {{ $education->degree_level_id == $item->id ? 'selected' : '' }}
                                        value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.DegreeTitle') }} <span
                                    class="text-danger">*</span></label>
                            <select id="degreeName" name="degree_name_id" class="form-control" required>
                                <option value="">{{ __('messages.DegreeTitle') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">{{ __('messages.InstituteName') }} <span class="text-danger">*</span></label>
                            <input type="text" name="institute_name" class="form-control"
                                value="{{ $education->institute_name ?? null }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Result') }}</label>
                            <input type="text" name="result" class="form-control"
                                value="{{ $education->result ?? null }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="control-label">{{ __('messages.Passing Year') }}</label>
                            <input type="text" name="pass_year" class="form-control"
                                value="{{ $education->pass_year ?? null }}">
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
