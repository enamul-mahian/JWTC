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
  // Status is based on the actual closing date, not publication alone.
  $today=now()->toDateString();
  $openJobCount=(clone $q)->whereDate('application_deadline','>=',$today)->count();
  $closedJobCount=(clone $q)->whereDate('application_deadline','<',$today)->count();
  $status=(string)$r->input('status','all');
  if($status==='open')$q->whereDate('application_deadline','>=',$today);
  if($status==='closed')$q->whereDate('application_deadline','<',$today);
  if($status==='unspecified')$q->whereNull('application_deadline');
  $jobs=$q->orderByRaw('CASE WHEN application_deadline >= ? THEN 0 WHEN application_deadline IS NULL THEN 1 ELSE 2 END',[$today])
   ->orderByDesc('created_at')->orderByDesc('id')->paginate(10)->withQueryString();
  $locations=\App\Models\Country::whereIn('id',\Illuminate\Support\Facades\DB::table('job_location_options')->where('enabled',true)->select('country_id'))->orderBy('name')->get(['id','name']);
  $job_categories=\App\Models\Dashboard\JobCategory::whereStatus(1)->orderBy('name')->get();
  // External government jobs are not employer-owned vacancies. Show them
  // separately with explicit attribution and links to the original publisher.
  $governmentJobs=collect();
  if (!$country_id && !$category_id && !$r->filled('salary_type')) {
   $external=\Illuminate\Support\Facades\DB::table('external_job_imports')
    ->where('source','uk_government_jobs')->where('status','published')
    ->where('application_deadline','>=',today()->toDateString())
    ->where('posted_on','>=',today()->subDays(14)->toDateString());
   if ($title!=='')$external->where(function($query)use($title){
    $search='%'.str_replace(['%','_'],['\\%','\\_'],$title).'%';
    $query->where('title','like',$search)->orWhere('company_name','like',$search)->orWhere('location','like',$search);
   });
   $governmentJobs=$external->orderByDesc('posted_on')->orderByDesc('id')->limit(6)->get();
  }
  return compact('jobs','locations','job_categories','title','country_id','category_id','governmentJobs','status','openJobCount','closedJobCount');
 }
}
