<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h6 class="modal-title" id="exampleModalLabel"> {{ __('messages.ApplyNow') }}</h6>
        </div>
        <form action="{{ route('save-job-application') }}" method="POST" id="application_form">
        @csrf 
        <input type="hidden" name="job_id" value="{{ $job->id }}">
        <input type="hidden" name="employer_id" value="{{ $job->company_id }}">
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">{{ __('messages.YourExpectedsalary') }} </label>
                        <input type="text" name="expected_salary" class="form-control" placeholder="{{ $job->minimum_salary }} - {{ $job->maximum_salary }}">
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
