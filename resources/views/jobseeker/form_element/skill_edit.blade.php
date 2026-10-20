<div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">{{ __('messages.Addnewskill') }}</h5>
        </div>
        <form action="{{ route('update-skill', $skill->id) }}" method="POST" id="form_data">
        @csrf 
        @method('PUT')
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.SkillName') }}    <span class="text-danger">*</span></label>
                        <input class="form-control" name="skill_name" placeholder="{{ __('messages.SkillnameEG') }}"
                            type="text" value="{{ $skill->skill_name }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.Proficiency') }}</label>
                        <input class="form-control" name="skill_proficiency"
                            placeholder="{{ __('messages.Skillproficiency') }}" type="text"
                            value="{{ $skill->skill_proficiency }}">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">{{ __('messages.Close') }}</button>
            <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.Save') }}</button>
        </div>
        </form>
    </div>
</div>
