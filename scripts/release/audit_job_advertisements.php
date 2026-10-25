<?php
// Read-only audit of PUBLIC job advertisement fields. Do not print applicant data.
try {
 $root=realpath('.');
 if (!$root || $root!==realpath(getenv('HOME')?:'').'/joingallagher.uk') throw new RuntimeException('wrong production root');
 require_once $root.'/vendor/autoload.php';
 $app=require $root.'/bootstrap/app.php';
 $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
 $db=Illuminate\Support\Facades\DB::connection();
 $out=['countries'=>$db->table('countries')->where(function($q){$q->where('name','like','%Ireland%')->orWhere('name','like','%Scotland%')->orWhere('name','like','%United Kingdom%');})->get(['id','name'])->toArray(),
 'others_categories'=>$db->table('job_categories')->whereRaw('LOWER(name)=?',['others'])->get(['id','name','status'])->toArray()];
 $out['jobs']=$db->table('jobs as j')->leftJoin('companies as c','j.company_id','=','c.id')->leftJoin('countries as n','j.country_id','=','n.id')->leftJoin('job_categories as cat','j.job_category_id','=','cat.id')
 ->whereNull('j.deleted_at')
 ->orderBy('j.id')
 ->get(['j.id','j.title','j.created_at','j.application_deadline','j.vacancies','j.minimum_salary','j.maximum_salary','j.salary_type','j.additional_salary_info','j.mini_age','j.max_age','j.country_id','n.name as country','j.location_detail','j.job_category_id','cat.name as category','j.logo_path','j.faqs','j.is_publish','c.company_name'])
 ->map(function($j){
 $faqs=json_decode((string)$j->faqs,true);
 return ['id'=>$j->id,'title'=>$j->title,'created'=>$j->created_at,'deadline'=>$j->application_deadline,'vacancies'=>$j->vacancies,
  'salary_min'=>$j->minimum_salary,'salary_max'=>$j->maximum_salary,'salary_type'=>$j->salary_type,'salary_note'=>mb_substr((string)$j->additional_salary_info,0,140),'age_min'=>$j->mini_age,'age_max'=>$j->max_age,
  'country_id'=>$j->country_id,'country'=>$j->country,'location_detail'=>$j->location_detail,'category_id'=>$j->job_category_id,'category'=>$j->category,
  'logo_path'=>$j->logo_path,'faq_count'=>is_array($faqs)?count($faqs):0,'published'=>$j->is_publish,'employer'=>$j->company_name];
 })->all();
 $out['summary']=['total'=>count($out['jobs']),'published'=>count(array_filter($out['jobs'],fn($j)=>$j['published']==1))];
 echo 'JOB_AUDIT_JSON '.json_encode($out,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE)."\n";
} catch(Throwable $e) {echo 'JOB_AUDIT_FAILED '.get_class($e)."\n";exit(1);}
