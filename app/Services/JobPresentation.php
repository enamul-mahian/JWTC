<?php
namespace App\Services;
use App\Models\Job;
use Illuminate\Http\Request;
class JobPresentation {
 public static function faqs(Job $job): array {
  $saved=json_decode((string)$job->faqs,true);
  if(is_array($saved))return $saved;
  $items=[];
  if($job->country?->name)$items[]=['question'=>'Where is this job based?','answer'=>'This role is based in '.$job->country->name.'.'];
  if($job->minimum_salary!==null || $job->maximum_salary!==null)$items[]=['question'=>'What is the advertised salary?','answer'=>(gs()?->currency_symbol??'').$job->minimum_salary.' – '.(gs()?->currency_symbol??'').$job->maximum_salary.' '.($job->salary_type??'').'.'];
  if($job->application_deadline)$items[]=['question'=>'When is the application deadline?','answer'=>substr((string)$job->application_deadline,0,10).'.'];
  $items[]=['question'=>'How do I apply?','answer'=>'Sign in to your JoinGallagher job seeker account and follow the application instructions.'];
  return $items;
 }
 public static function fields(Request $request,array $data,?Job $job=null): array {
  unset($data['logo'],$data['remove_logo'],$data['faq_question'],$data['faq_answer']);
  if($request->boolean('remove_logo'))$data['logo_path']=null;
  if($request->hasFile('logo')){$file=$request->file('logo');$dir=public_path('assets/images/job-logos');if(!is_dir($dir))mkdir($dir,0755,true);$name=\Illuminate\Support\Str::random(40).'.'.$file->extension();$file->move($dir,$name);$data['logo_path']='job-logos/'.$name;}
  if($request->boolean('faq_editor') || $request->has('faq_question')){
   $items=[];foreach($request->input('faq_question',[]) as $i=>$question){$answer=$request->input('faq_answer.'.$i);if(trim((string)$question)!=='' && trim((string)$answer)!=='')$items[]=['question'=>trim($question),'answer'=>trim($answer)];}
   $data['faqs']=json_encode($items,JSON_THROW_ON_ERROR);
  }else{$draft=$job?clone $job:new Job();$draft->fill($data);$draft->unsetRelation('country');$data['faqs']=json_encode(self::faqs($draft),JSON_THROW_ON_ERROR);}
  return $data;
 }
 public static function logo(Job $job): ?string {
  $path=$job->logo_path;
  return is_string($path) && preg_match('#^job-logos/[a-zA-Z0-9._-]+\.(png|jpe?g|webp)$#i',$path)?asset('assets/images/'.$path):null;
 }
}
