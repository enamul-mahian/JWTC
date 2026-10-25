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
  $estimated=str_contains((string)$job->additional_salary_info,'2026 indicative salary benchmark');
  $symbol=$job->country?->name==='Ireland' ? '€' : (string)(gs()?->currency_symbol ?? '£');
  $range=$symbol.number_format((float)($min??$max),2).' – '.$symbol.number_format((float)($max??$min),2);
  if($estimated) {
   $scope=$job->country?->name==='Antigua and Barbuda'
     ? 'UK comparison only; NOT a local wage rate or an employer pay offer'
     : 'indicative 2026 market benchmark, NOT employer-confirmed pay';
   return $range.' per '.($period==='yearly' ? 'year' : ($period==='hourly' ? 'hour' : $period)).
     ' ('.$scope.'; gross; verify with the employer)';
  }
  if($period==='hourly')return $range.' per hour (gross, advertised rate; employer confirmation required)';
  // Old records frequently have very small numbers marked monthly/weekly.
  // A falsely labelled period would mislead applicants. Fail closed in public UI.
  if(in_array($period,['weekly','monthly','yearly'],true) && (float)($max??$min)<100)
   return 'Pay period requires employer confirmation; contact the employer for an accurate rate';
  return $range.' per '.($period?:'unspecified period').' (verify with the employer)';
 }
 /**
  * Public and editor FAQs are ALWAYS the saved, user-controlled rows.
  * No automatic suggestions are inserted at render time: an empty JSON
  * list intentionally means the administrator removed every FAQ.
  */
 public static function faqs(Job $job): array {
  $saved=json_decode((string)$job->faqs,true);
  if(!is_array($saved))return [];
  $items=[];
  $seen=[];
  foreach($saved as $faq){
   if(!is_array($faq))continue;
   $question=trim((string)($faq['question']??''));
   $answer=trim((string)($faq['answer']??''));
   if($question===''||$answer==='')continue;
   $key=mb_strtolower($question);
   if(isset($seen[$key]))continue;
   $seen[$key]=true;
   // The saved FAQ is the source of truth. Never rewrite answers while rendering.
   $items[]=['question'=>$question,'answer'=>$answer];
   if(count($items)>=12)break;
  }
  return $items;
 }

 /**
  * Optional *suggestions* derived from a job, never rendered or stored
  * without a separate, explicit user action. This is kept as a helper for
  * possible preview tools, not a fallback for deleted FAQs.
  */
 public static function suggestedFaqs(Job $job): array {
  $saved=json_decode((string)$job->faqs,true);
  $items=is_array($saved) ? array_values(array_filter($saved,static fn($f)=>is_array($f)&&!empty($f['question'])&&!empty($f['answer']))) : [];
  if(str_contains((string)$job->additional_salary_info,'2026 indicative salary benchmark')) {
   $items=array_values(array_filter($items,static fn($f)=>!preg_match('/salary|pay|wage|earnings|monthly|hourly|annual|per hour/i',(string)$f['question'])));
  }
  $country=(string)($job->country?->name??'');
  $period=strtolower(trim((string)$job->salary_type));
  $estimated=str_contains((string)$job->additional_salary_info,'2026 indicative salary benchmark');
  $symbol=$job->country?->name==='Ireland' ? '€' : (string)(gs()?->currency_symbol??'£');
  $monthly='Not calculated: the advertised pay period or hours need confirmation from the employer.';
  if($estimated && $period==='yearly' && $job->minimum_salary!==null && $job->maximum_salary!==null){
   $monthly='Indicative gross monthly equivalent of the benchmark: '.
    $symbol.number_format((float)$job->minimum_salary/12,2).' – '.
    $symbol.number_format((float)$job->maximum_salary/12,2).
    ' (annual benchmark ÷ 12). This is not an employer pay offer.';
  }
  if($period==='hourly' && $job->minimum_salary!==null && $job->maximum_salary!==null){
   $symbol=$job->country?->name==='Ireland' ? '€' : (string)(gs()?->currency_symbol??'£');
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
   ['question'=>$estimated?'What is the indicative 2026 salary benchmark?':'What pay is advertised and what does it mean?',
    'answer'=>self::salarySummary($job).'. '.$regulatory.'This is a market reference, not a confirmed vacancy offer. Confirm actual pay, currency and contracted hours with the employer.'],
   ['question'=>'How much is that per month before tax?','answer'=>$monthly],
   ['question'=>'Where is the job based?','answer'=>$country!=='' ? 'Recorded location: '.$country.($job->location_detail?' — '.$job->location_detail:'').'. Confirm the exact worksite and employment jurisdiction with the employer.' : 'The exact worksite must be confirmed with the employer.'],
   ['question'=>'How many positions and when does this job close?','answer'=>'Recorded vacancies: '.($job->vacancies ?: 'not specified').
      '. Listed application deadline: '.($job->application_deadline ? substr((string)$job->application_deadline,0,10) : 'not specified').
      '. '.(\App\Services\JobDateRevision::note($job)
         ?? 'Check directly with the employer that these vacancies are still recruiting before you apply.')],
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
  if($request->boolean('faq_editor') || $request->has('faq_question') || $request->has('faq_answer')){
   // Empty submitted editor with no rows is an explicit request to clear FAQs.
   $items=[];
   foreach($request->input('faq_question',[]) as $i=>$question){
    $answer=$request->input('faq_answer.'.$i);
    if(trim((string)$question)!=='' && trim((string)$answer)!=='')
     $items[]=['question'=>trim((string)$question),'answer'=>trim((string)$answer)];
   }
   $data['faqs']=json_encode(array_slice($items,0,12),JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
  } elseif(!$job) {
   // No editor was submitted during creation: do not invent FAQs.
   $data['faqs']='[]';
  }
  // On unrelated edits without the FAQ editor, preserve the saved value.
  // Optional source/application metadata is validated by the shared job request.
  return $data;
 }
 public static function logo(Job $job): ?string {
  $path=$job->logo_path;
  return is_string($path) && preg_match('#^job-logos/[a-zA-Z0-9._-]+\.(png|jpe?g|webp)$#i',$path)?asset('assets/images/'.$path):null;
 }
}
