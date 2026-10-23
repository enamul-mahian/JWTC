<?php
namespace App\Services;
use App\Models\Job;
use Illuminate\Http\Request;
class JobSearch {
 public function data(Request $r): array {
  $r->validate(['title'=>'nullable|string|max:120','country_id'=>'nullable|integer','location'=>'nullable|integer','category_id'=>'nullable|integer','salary_type'=>'nullable|string|in:hourly,daily,weekly,monthly,yearly','page'=>'nullable|integer|min:1']);
  $title=trim((string)$r->input('title',''));$country_id=$r->input('country_id')?:$r->input('location');$category_id=$r->input('category_id');
  $q=Job::with(['company','country','job_category'])->where('is_publish',1);
  if($title!=='')$q->where(function($q)use($title){$needle='%'.mb_strtolower($title).'%';$q->whereRaw('LOWER(title) LIKE ?',[$needle])->orWhereRaw('LOWER(job_context) LIKE ?',[$needle])->orWhereRaw('LOWER(job_responsibilities) LIKE ?',[$needle])->orWhereHas('company',fn($c)=>$c->whereRaw('LOWER(company_name) LIKE ?',[$needle]));});
  if($country_id)$q->where('country_id',$country_id);
  if($category_id)$q->where('job_category_id',$category_id);
  if($r->filled('salary_type'))$q->whereRaw('LOWER(TRIM(salary_type)) = ?',[$r->input('salary_type')]);
  $jobs=$q->orderByDesc('created_at')->orderByDesc('id')->paginate(10)->withQueryString();
  $locations=\App\Models\Country::whereIn('id',\Illuminate\Support\Facades\DB::table('job_location_options')->where('enabled',true)->select('country_id'))->orderBy('name')->get(['id','name']);
  $job_categories=\App\Models\Dashboard\JobCategory::whereStatus(1)->orderBy('name')->get();
  return compact('jobs','locations','job_categories','title','country_id','category_id');
 }
}
