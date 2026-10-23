<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header"><h6 class="modal-title">Apply for this job</h6></div>
<div class="modal-body">
<p><strong>Do you have a unique application code?</strong></p>
<details>
<summary class="btn btn-primary">Yes, I have a code</summary>
<form action="{{ route('save-job-application') }}" method="POST" id="application_form" style="margin-top:16px">
@csrf
<input type="hidden" name="job_id" value="{{ $job->id }}">
<input type="hidden" name="employer_id" value="{{ $job->company_id }}">
<label for="application-code">Enter your unique application code</label>
<input id="application-code" name="application_code" type="text" class="form-control" required maxlength="100" autocomplete="off">
<p>Each code can be used once for one job application. Only codes assigned to your account are accepted.</p>
<label for="expected-salary">Expected salary</label>
<input id="expected-salary" name="expected_salary" type="number" min="0" max="999999.99" step="0.01" class="form-control" required>
<button type="submit" class="btn btn-primary" style="margin-top:16px">Submit application</button>
</form>
</details>
<a class="btn btn-secondary" style="margin-top:16px" href="{{ route('unique_codes') }}">No, request a code</a>
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></div>
</div>
</div>
