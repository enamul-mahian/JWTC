@php
$lists=function($name)use($job){$value=old($name,$job->{$name});if(is_array($value))return array_values(array_filter($value,'is_scalar'));$decoded=json_decode((string)$value,true);if(is_array($decoded))return array_values(array_filter($decoded,'is_scalar'));if($name==='other_benefits' && trim((string)$value)!=='' && $value!=='null')return [is_scalar($decoded)?(string)$decoded:(string)$value];return [];};
@endphp
<fieldset class="jg-editor-panel"><legend>1. Job overview</legend><div class="jg-form-grid">
<label class="jg-field-wide">Job title<input name="title" maxlength="255" value="{{ old('title',$job->title) }}" required></label>
<label>Vacancies<input name="vacancies" type="number" min="1" value="{{ old('vacancies',$job->vacancies??1) }}" required></label>
<label>Application deadline<input name="application_deadline" type="date" value="{{ old('application_deadline',substr((string)$job->application_deadline,0,10)) }}" required></label>
<label>Job category<select name="job_category_id" required><option value="">Select a category</option>@foreach($jobCategory as $category)<option value="{{ $category->id }}" @selected(old('job_category_id',$job->job_category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select></label>
<label>Job location<select name="country_id" required><option value="">Select a country</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('country_id',$job->country_id)==$location->id)>{{ $location->name }}</option>@endforeach</select></label>
<fieldset class="jg-choice-group"><legend>Job types</legend><div class="jg-checkbox-grid">@foreach($jobTypes as $type)<label><input type="checkbox" name="employment_status[]" value="{{ $type->id }}" @checked(in_array($type->id,$lists('employment_status')))><span>{{ $type->name }}</span></label>@endforeach</div><small>Select all that apply.</small></fieldset>
<fieldset class="jg-choice-group"><legend>Skills</legend><div class="jg-checkbox-grid">@foreach($availableSkills->groupBy('skill_name')->map(fn($group)=>$group->first(fn($item)=>in_array($item->id,$lists('skills')))??$group->first()) as $skill)<label><input type="checkbox" name="skills[]" value="{{ $skill->id }}" @checked(in_array($skill->id,$lists('skills')))><span>{{ $skill->skill_name }}</span></label>@endforeach</div><small>Select all that apply.</small></fieldset>
<fieldset class="jg-choice-group"><legend>Job level</legend><div class="jg-checkbox-grid">@foreach(array_unique(array_merge(['entry','mid','top'],$lists('job_level'))) as $level)<label><input type="checkbox" name="job_level[]" value="{{ $level }}" @checked(in_array($level,$lists('job_level')))><span>{{ ucfirst($level) }}</span></label>@endforeach</div><small>Select all that apply.</small></fieldset>
<fieldset class="jg-choice-group"><legend>Workplace</legend><div class="jg-checkbox-grid">@foreach(array_unique(array_merge(['work_at_office','work_from_home'],$lists('work_place'))) as $place)<label><input type="checkbox" name="work_place[]" value="{{ $place }}" @checked(in_array($place,$lists('work_place')))><span>{{ ucwords(str_replace('_',' ',$place)) }}</span></label>@endforeach</div><small>Select all that apply.</small></fieldset>
<label class="jg-field-wide">Specific job location (optional)<input name="location_detail" maxlength="255" value="{{ old('location_detail',$job->location_detail) }}" placeholder="e.g. London, England"></label>
<label class="jg-field-wide">Original vacancy URL (optional)<input name="source_url" type="url" maxlength="2048" value="{{ old('source_url',$job->source_url) }}" placeholder="https://..."><small>Provide the original job listing URL if this vacancy is sourced externally.</small></label>
<label class="jg-field-wide">Apply on employer website (optional)<input name="external_apply_url" type="url" maxlength="2048" value="{{ old('external_apply_url',$job->external_apply_url) }}" placeholder="https://..."><small>When provided, applicants can follow the original employer application link.</small></label>
<label class="jg-field-wide">Application contact email<input name="resume_receiving_email" type="email" maxlength="255" value="{{ old('resume_receiving_email',$job->resume_receiving_email??auth()->user()->email) }}" required><small>Public visibility is controlled by the administrator.</small></label>
</div></fieldset>
<fieldset class="jg-editor-panel"><legend>2. Responsibilities and salary</legend><div class="jg-form-grid">
<div class="jg-field-wide jg-rich-field"><label id="label-job_context">Job summary</label><div class="jg-rich-toolbar" role="toolbar" aria-label="Job summary formatting"><button type="button" data-command="bold"><strong>B</strong></button><button type="button" data-command="italic"><em>I</em></button><button type="button" data-command="insertUnorderedList">Bullet list</button><button type="button" data-command="insertOrderedList">Numbered list</button><button type="button" data-command="removeFormat">Clear formatting</button></div><div class="jg-rich-editor" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="label-job_context" data-editor="job_context">{!! app(\App\Services\JobContent::class)->render(old('job_context',$job->job_context)) !!}</div><textarea hidden name="job_context">{{ old('job_context',$job->job_context) }}</textarea></div>
<div class="jg-field-wide jg-rich-field"><label id="label-job_responsibilities">Responsibilities</label><div class="jg-rich-toolbar" role="toolbar" aria-label="Responsibilities formatting"><button type="button" data-command="bold"><strong>B</strong></button><button type="button" data-command="italic"><em>I</em></button><button type="button" data-command="insertUnorderedList">Bullet list</button><button type="button" data-command="insertOrderedList">Numbered list</button><button type="button" data-command="removeFormat">Clear formatting</button></div><div class="jg-rich-editor" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="label-job_responsibilities" data-editor="job_responsibilities">{!! app(\App\Services\JobContent::class)->render(old('job_responsibilities',$job->job_responsibilities)) !!}</div><textarea hidden name="job_responsibilities">{{ old('job_responsibilities',$job->job_responsibilities) }}</textarea></div>
<label>Minimum annual gross salary<input type="number" name="minimum_salary" min="0" value="{{ old('minimum_salary',$job->minimum_salary) }}" required></label>
<label>Maximum annual gross salary<input type="number" name="maximum_salary" min="0" value="{{ old('maximum_salary',$job->maximum_salary) }}" required></label>
<label>Salary basis<input value="Yearly (gross)" readonly><input type="hidden" name="salary_type" value="yearly"></label>
<label>Weekly contracted working hours<input id="jg-weekly-hours" name="weekly_working_hours" type="number" min="1" max="80" step="0.5" value="{{ old('weekly_working_hours',\App\Services\JobPresentation::weeklyHours($job)) }}" placeholder="e.g. 40"><small>Used only to calculate indicative hourly equivalent.</small></label>
<div class="jg-field-wide" id="jg-salary-preview" aria-live="polite" style="padding:15px;border:1px solid #cad8ec;border-radius:10px;background:#f6f9fd">
<strong>Automatic gross pay equivalents</strong>
<p id="jg-salary-preview-values">Enter annual minimum, maximum and weekly hours.</p>
<small>Annual salary is the source of truth. Payment frequency: To Be Confirmed with employer. Weekly = annual ÷ 52; monthly = annual ÷ 12; hourly = annual ÷ (52 × hours).</small>
</div>
@if(!empty($adminCreate))
<label class="jg-field-wide">Publishing employer (approved account)
<select name="company_id" required>
<option value="">Select an approved employer</option>
@foreach($companies as $postingCompany)
<option value="{{ $postingCompany->id }}" @selected(old('company_id')==$postingCompany->id)>{{ $postingCompany->company_name }}</option>
@endforeach
</select>
<small>The job belongs to this employer account. Confirm their permission before publishing.</small>
</label>
@else
<label>Company<input value="{{ $company?->company_name ?? 'Company not available' }}" readonly><input type="hidden" name="company_id" value="{{ $company?->id }}"></label>
@endif
<label class="jg-field-wide">Additional salary information<textarea name="additional_salary_info" rows="3">{{ old('additional_salary_info',$job->additional_salary_info) }}</textarea></label>
<label class="jg-field-wide">Other benefits<textarea name="benefits_text" rows="3">{{ old('benefits_text',implode("\n",$lists('other_benefits'))) }}</textarea><small>One benefit per line.</small></label>
@foreach(['lunch_facilities'=>'Lunch facilities','salary_review'=>'Salary review','festival_bonus'=>'Festival bonus','professional_certification'=>'Professional certification','trade_course'=>'Trade course'] as $field=>$label)<label>{{ $label }}<input name="{{ $field }}" value="{{ old($field,$job->{$field}) }}" @if($field==='festival_bonus') type="number" min="0" @endif></label>@endforeach
</div></fieldset>
<fieldset class="jg-editor-panel"><legend>3. Candidate requirements</legend><div class="jg-form-grid">
<label>Minimum age<input name="mini_age" type="number" min="16" max="100" value="{{ old('mini_age',$job->mini_age??18) }}" required></label>
<label>Maximum age<input name="max_age" type="number" min="16" max="100" value="{{ old('max_age',$job->max_age) }}"></label>
<label>Education level<select name="degree_level_id"><option value="">Not specified</option>@foreach($degreeLevel as $degree)<option value="{{ $degree->id }}" @selected(old('degree_level_id',$job->degree_level_id)==$degree->id)>{{ $degree->name }}</option>@endforeach</select></label>
<label>Degree
<select name="degree_name_id">
<option value="">Not specified</option>
@foreach($degreeNames->groupBy('degree_lavel_id') as $levelId => $levelDegrees)
<optgroup label="{{ $degreeLevel->firstWhere('id', $levelId)?->name ?? 'Other education level' }}">
@foreach($levelDegrees as $degree)
<option value="{{ $degree->id }}" @selected(old('degree_name_id',$job->degree_name_id)==$degree->id)>{{ $degree->name }}</option>
@endforeach
</optgroup>
@endforeach
</select>
@if(!empty($adminEdit) || !empty($adminCreate))
<small><a href="{{ route('admin.job-degrees.index') }}" target="_blank" rel="noopener noreferrer">Add, rename or disable Degree options</a> (opens a new tab; reload this form after changes).</small>
@endif
</label>
<label>Minimum experience (years)<input name="minimum_year" type="number" min="0" value="{{ old('minimum_year',$job->minimum_year) }}"></label>
<label>Maximum experience (years)<input name="maximum_year" type="number" min="0" value="{{ old('maximum_year',$job->maximum_year) }}"></label>
<label>Freshers<input name="fresher" maxlength="255" value="{{ old('fresher',$job->fresher) }}" placeholder="For example: welcome to apply"></label>
<fieldset class="jg-choice-group"><legend>Gender requirements</legend><div class="jg-checkbox-grid">@foreach(array_unique(array_merge(['male','female','other'],$lists('gender'))) as $gender)<label><input type="checkbox" name="gender[]" value="{{ $gender }}" @checked(in_array($gender,$lists('gender')))><span>{{ ucfirst($gender) }}</span></label>@endforeach</div><small>Select all that apply.</small></fieldset>
<label class="jg-field-wide">Additional requirements<textarea name="additional_requirements" rows="4">{{ old('additional_requirements',$job->additional_requirements) }}</textarea></label>
</div></fieldset>

<fieldset class="jg-editor-panel"><legend>4. Brand and frequently asked questions</legend><div class="jg-form-grid"><label>Job / company logo<input name="logo" type="file" accept="image/png,image/jpeg,image/webp"><small>PNG, JPG or WebP, up to 2 MB.</small></label><div>@if(\App\Services\JobPresentation::logo($job))<img class="jg-job-logo" src="{{ \App\Services\JobPresentation::logo($job) }}" alt="Current job logo"><label><input type="checkbox" name="remove_logo" value="1"> Remove current logo</label>@endif</div></div>
<p>Only FAQs saved below will appear on this job. Remove any row (or remove all rows) and click Save to delete it permanently. <strong>Generate from current details</strong> replaces the editor's current FAQ rows with fresh suggestions from the current job fields. Review them before saving.</p>
<input type="hidden" name="faq_editor" value="1"><div data-faq-rows>@php($faqRows=old('faq_editor')!==null ? collect(old('faq_question',[]))->map(fn($q,$i)=>['question'=>$q,'answer'=>old('faq_answer.'.$i,'')])->all() : \App\Services\JobPresentation::faqs($job))
@foreach($faqRows as $faq)<div class="jg-faq-row"><label>Question<input name="faq_question[]" maxlength="250" value="{{ $faq['question'] }}"></label><label>Answer<textarea name="faq_answer[]" maxlength="2000" rows="2">{{ $faq['answer'] }}</textarea></label><button type="button" data-remove-faq>Remove FAQ</button></div>@endforeach</div>
<button type="button" class="jg-button-secondary" data-add-faq>Add FAQ</button> <button type="button" class="jg-button-secondary" data-generate-faq>Generate from current details</button><small data-faq-feedback role="status"></small>
</fieldset>
@once @push('script')<script defer src="{{ asset('theme/job-editor.js') }}?v=20261026-faq-saved-content-2"></script>@endpush @endonce

@push('script')
<script>
(function(){
  const root=document.querySelector('#jg-weekly-hours')?.closest('form');
  if(!root) return;
  const min=root.querySelector('[name=minimum_salary]');
  const max=root.querySelector('[name=maximum_salary]');
  const hours=root.querySelector('[name=weekly_working_hours]');
  const out=document.getElementById('jg-salary-preview-values');
  const money=v=>(root.querySelector('[name=country_id] option:checked')?.textContent?.trim()==='Ireland'?'€':'£')+v.toLocaleString('en-GB',{minimumFractionDigits:2,maximumFractionDigits:2});
  function refresh(){
    const a=Number(min.value),b=Number(max.value),h=Number(hours.value);
    if(!min.value||!max.value||!hours.value||!Number.isFinite(a)||!Number.isFinite(b)||!Number.isFinite(h)||a<0||b<a||h<1||h>80){
      out.textContent='Enter valid annual minimum, maximum and weekly hours.';
      return;
    }
    out.textContent='Yearly: '+money(a)+' – '+money(b)+' | Monthly equivalent: '+money(a/12)+' – '+money(b/12)+' | Weekly equivalent: '+money(a/52)+' – '+money(b/52)+' | Hourly equivalent: '+money(a/(52*h))+' – '+money(b/(52*h))+' ('+h+' hours/week).';
  }
  [min,max,hours].forEach(x=>x.addEventListener('input',refresh));refresh();
})();
</script>
@endpush
