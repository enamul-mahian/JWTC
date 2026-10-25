<?php
namespace App\Services;
use App\Models\Job;
use Illuminate\Http\Request;
class JobPresentation {
 public static function salarySummary(Job $job): string {
  $min=$job->minimum_salary;
  $max=$job->maximum_salary;
  if($min===null && $max===null)return 'Pay not stated; verify with the employer';
  $period=strtolower(trim((string)$job->salary_type));
  $symbol=(string)(gs()?->currency_symbol ?? '£');
  $range=$symbol.number_format((float)($min??$max),2).' – '.$symbol.number_format((float)($max??$min),2);
  if($period==='hourly')return $range.' per hour (gross, advertised rate; employer confirmation required)';
  // Old records frequently have very small numbers marked monthly/weekly.
  // A falsely labelled period would mislead applicants. Fail closed in public UI.
  if(in_array($period,['weekly','monthly','yearly'],true) && (float)($max??$min)<100)
   return 'Pay period requires employer confirmation; contact the employer for an accurate rate';
  return $range.' per '.($period?:'unspecified period').' (verify with the employer)';
 }
 public static function faqs(Job $job): array {
  $saved=json_decode((string)$job->faqs,true);
  $items=is_array($saved) ? array_values(array_filter($saved,static fn($f)=>is_array($f)&&!empty($f['question'])&&!empty($f['answer']))) : [];
  $country=(string)($job->country?->name??'');
  $period=strtolower(trim((string)$job->salary_type));
  $monthly='Not calculated: the advertised pay period or hours need confirmation from the employer.';
  if($period==='hourly' && $job->minimum_salary!==null && $job->maximum_salary!==null){
   $symbol=(string)(gs()?->currency_symbol??'£');
   $weeklyHours=37.5;
   $a=$symbol.number_format((float)$job->minimum_salary*$weeklyHours*52/12,2);
   $b=$symbol.number_format((float)$job->maximum_salary*$weeklyHours*52/12,2);
   $monthly='Indicative gross monthly equivalent: '.$a.' – '.$b.
    ', assuming 37.5 paid hours per week and 52 weeks per year (hourly rate × 37.5 × 52 ÷ 12). '.
    'This is not a confirmed monthly salary. Actual pay varies with hours, paid leave and deductions.';
  }
  $regulatory='';
  if(in_array($country,['United Kingdom','Scotland'],true)){
    $regulatory='UK legal reference from April 2026: £12.71 per hour for workers aged 21 and over (different age and apprenticeship rules may apply). '.
      'Any advertised rate below the applicable legal minimum requires correction by the employer. ';
  } elseif($country==='Ireland'){
    $regulatory='For the Republic of Ireland, the national minimum is €14.15 per hour for workers aged 20 and over from January 2026 (some sector and age exceptions apply). '.
      'Confirm EUR versus GBP currency and the employer-approved pay before relying on this advertisement. ';
  }
  $facts=[
   ['question'=>'What pay is advertised and what does it mean?','answer'=>self::salarySummary($job).'. '.$regulatory.'Confirm the pay period, currency and contracted hours with the employer.'],
   ['question'=>'How much is that per month before tax?','answer'=>$monthly],
   ['question'=>'Where is the job based?','answer'=>$country!=='' ? 'Recorded location: '.$country.($job->location_detail?' — '.$job->location_detail:'').'. Confirm the exact worksite and employment jurisdiction with the employer.' : 'The exact worksite must be confirmed with the employer.'],
   ['question'=>'How many positions and when does this job close?','answer'=>'Recorded vacancies: '.($job->vacancies ?: 'not specified').
      '. Listed application deadline: '.($job->application_deadline ? substr((string)$job->application_deadline,0,10) : 'not specified').
      '. Check directly with the employer that these vacancies are still recruiting before you apply.'],
  ];
  $normalized=[];
  foreach(array_merge($facts,$items) as $faq){
   $key=mb_strtolower(trim((string)($faq['question']??'')));
   if($key==='' || isset($normalized[$key]))continue;
   $normalized[$key]=['question'=>(string)$faq['question'],'answer'=>(string)$faq['answer']];
   if(count($normalized)>=12)break;
  }
  return array_values($normalized);
 }
 public static function fields(Request $request,array $data,?Job $job=null): array {
  unset($data['logo'],$data['remove_logo'],$data['faq_question'],$data['faq_answer']);
  if($request->boolean('remove_logo'))$data['logo_path']=null;
  if($request->hasFile('logo')){$file=$request->file('logo');$dir=public_path('assets/images/job-logos');if(!is_dir($dir))mkdir($dir,0755,true);$name=\Illuminate\Support\Str::random(40).'.'.$file->extension();$file->move($dir,$name);$data['logo_path']='job-logos/'.$name;}
  if($request->boolean('faq_editor') || $request->has('faq_question')){
   $items=[];foreach($request->input('faq_question',[]) as $i=>$question){$answer=$request->input('faq_answer.'.$i);if(trim((string)$question)!=='' && trim((string)$answer)!=='')$items[]=['question'=>trim($question),'answer'=>trim($answer)];}
   $data['faqs']=json_encode($items,JSON_THROW_ON_ERROR);
  }else{$draft=$job?clone $job:new Job();$draft->fill($data);$draft->unsetRelation('country');$data['faqs']=json_encode(self::faqs($draft),JSON_THROW_ON_ERROR);}
  // Optional source/application metadata is validated by the shared job request.
  return $data;
 }
 public static function logo(Job $job): ?string {
  $path=$job->logo_path;
  return is_string($path) && preg_match('#^job-logos/[a-zA-Z0-9._-]+\.(png|jpe?g|webp)$#i',$path)?asset('assets/images/'.$path):null;
 }
}
