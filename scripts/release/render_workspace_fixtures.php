<?php
// Synthetic UI fixtures only. No account, session or customer row is persisted.
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver'=>'array','cache.default'=>'array','mail.default'=>'array']);
Illuminate\Support\Facades\Gate::before(fn()=>true);
Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8765');
Illuminate\Support\Facades\URL::forceScheme('http');
$user=new App\Models\User(['name'=>'Arif Rahman','email'=>'arif@example.com','user_type'=>'job_seeker']);
$user->id=999; $user->email_verified_at=now();
$info=new App\Models\PersonalInformation(['motto'=>'Hospitality Professional','about_me'=>'Dedicated hospitality professional with experience in kitchen operations and customer service. Committed to excellent service and a positive working environment.','phone'=>'+351 900 000 000','alternate_phone'=>'+351 900 000 000','email'=>'arif@example.com','alternate_email'=>'arif@example.com','present_city'=>'Lisbon','present_country'=>'Portugal']);
$user->setRelation('personal_info',$info);
$user->setRelation('work_experiences',collect([
new App\Models\WorkExperience(['position_name'=>'Kitchen Assistant','employeer'=>'Example Restaurant','city'=>'Lisbon','occupation_country'=>'Portugal','from_date'=>'2023-01-01','continuing'=>1,'responsibilities'=>'Supported daily kitchen operations, food preparation and food safety.']),
new App\Models\WorkExperience(['position_name'=>'Service Assistant','employeer'=>'City Cafe','city'=>'Warsaw','occupation_country'=>'Poland','from_date'=>'2022-01-01','to_date'=>'2022-12-31','continuing'=>0,'responsibilities'=>'Assisted customers and supported the service team.'])
]));
$education=new App\Models\Education(['institute_name'=>'Open University','pass_year'=>'2017','result'=>'2.51']);
$education->setRelation('degreeName',new App\Models\Dashboard\DegreeName(['name'=>'Bachelor of Science']));
$education->setRelation('degreeLevel',null);
$user->setRelation('educations',collect([$education]));
$user->setRelation('skills',collect(array_map(fn($s)=>new App\Models\JobseekerSkill(['skill_name'=>$s,'skill_proficiency'=>'Experienced']),['Food Preparation','Customer Service','Teamwork','Food Safety'])));
$user->setRelation('languages',collect(array_map(fn($s)=>new App\Models\Language(['language_name'=>$s,'listening_proficiency'=>'Intermediate','reading_proficiency'=>'Intermediate','writting_proficiency'=>'Intermediate']),['English','Portuguese','Hindi'])));
$user->setRelation('trainings',collect());
Illuminate\Support\Facades\Auth::setUser($user);
$session=app('session')->driver('array'); $session->start();
Illuminate\Support\Facades\View::share(['setting'=>null,'pages'=>[],'errors'=>new Illuminate\Support\ViewErrorBag()]);
$out=getenv('JWTC_UI_FIXTURES'); if(!$out)throw new RuntimeException('Fixture output required');
if(!is_dir($out))mkdir($out,0700,true);
$headers=[];
foreach([
'profile'=>['/job_seeker/view-jobseeker-profile','jobseeker.dashboard.profile_show',['user'=>$user,'passportSummary'=>['number'=>'••••••4821','country'=>'Bangladesh']]],
'documents'=>['/job_seeker/private-documents','secure.documents.applicant_index',['documents'=>new Illuminate\Pagination\LengthAwarePaginator([],0,12)]],
'interviews'=>['/job_seeker/interviews','interviews.applicant_index',['applications'=>new Illuminate\Pagination\LengthAwarePaginator([],0,15)]],
] as $name=>[$path,$view,$data]){
$request=Illuminate\Http\Request::create('http://127.0.0.1:8765'.$path);
$request->setLaravelSession($session); $request->setUserResolver(fn()=>$user);
$route=Illuminate\Support\Facades\Route::getRoutes()->match($request);$request->setRouteResolver(fn()=>$route);$app->instance('request',$request);
$html=view($view,$data)->render();
if(substr_count($html,'arif@example.com')>1)throw new RuntimeException('Duplicate profile contact');
file_put_contents($out.'/'.$name.'.html',$html);
if($name!=='profile'){
$response=(new App\Http\Middleware\PrivateDocumentResponseHeaders())->handle($request,fn()=>new Symfony\Component\HttpFoundation\Response($html,200,['Content-Type'=>'text/html']));
$headers[$path]=$response->headers->all();
}
echo 'UI_FIXTURE '.$name." OK\n";
}
file_put_contents($out.'/headers.json',json_encode($headers,JSON_THROW_ON_ERROR));
