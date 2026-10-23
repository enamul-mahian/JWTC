<?php
// Console-only template verification. Uses existing authorization in memory,
// an array session/cache and a READ ONLY DB transaction. No login session,
// account, permission, message or customer record is created or modified.
ini_set('display_errors','0');ini_set('log_errors','0');$phase='bootstrap';$db=null;
try {
    $root=realpath('.');$portalHome=realpath(getenv('HOME'));
    if(!in_array($root,[$portalHome.'/joingallagher.uk',$portalHome.'/jwtc-private-staging/20261022-215145-f92ca979/application'],true))throw new RuntimeException('Unexpected root');
    require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    config(['support_center.enabled'=>true,'email_studio.enabled'=>true,'session.driver'=>'array','cache.default'=>'array','mail.default'=>'array','queue.default'=>'null']);
    $session=app('session')->driver('array');$session->start();
    Illuminate\Support\Facades\View::share(['errors'=>new Illuminate\Support\ViewErrorBag(),'setting'=>gs(),'pages'=>[]]);
    $db=Illuminate\Support\Facades\DB::connection();$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
    $render=static function(string $label,string $path,$actor,callable $handler)use($app,$session,&$phase){
        $phase=$label;Illuminate\Support\Facades\Auth::setUser($actor);
        $request=Illuminate\Http\Request::create('https://joingallagher.uk'.$path,'GET');$request->setUserResolver(fn()=>$actor);$request->setLaravelSession($session);
        $route=Illuminate\Support\Facades\Route::getRoutes()->match($request);$request->setRouteResolver(fn()=>$route);$app->instance('request',$request);
        $phase=$label.'-controller';$response=$handler($request);$phase=$label.'-render';if($response instanceof Illuminate\Contracts\View\View)$response->render();elseif($response instanceof Symfony\Component\HttpFoundation\Response){if($response->getStatusCode()>=400)throw new RuntimeException('Template response failed');$response->getContent();}
        echo 'READONLY_VIEW '.$label." OK\n";
    };
    $admins=App\Models\User::whereRaw('LOWER(user_type) IN (?,?)',['admin','super_admin'])->get();
    $admin=$admins->first(fn($user)=>$user->can('active-job'));
    if($admin){
        $editor=app(App\Http\Controllers\Admin\JobEditorController::class);
        $render('admin-jobs','/admin/jobs',$admin,fn($request)=>$editor->index($request));
        $render('workflow-settings','/admin/application-statuses',$admin,fn($request)=>app(App\Http\Controllers\Admin\ApplicationStatusDefinitionController::class)->index($request,app(App\Services\ApplicationStatusCatalog::class)));
        $progress=app(App\Http\Controllers\Admin\ApplicationProgressController::class);
        $render('admin-applications','/admin/applications',$admin,fn($request)=>$progress->index($request,app(App\Services\ApplicationStatusCatalog::class)));
        $job=App\Models\Job::latest('id')->first();if($job)$render('admin-job-editor','/admin/jobs/'.$job->id.'/edit',$admin,fn($request)=>$editor->edit($request,$job,app(App\Services\JobEditorData::class)));
        $application=App\Models\Application::latest('id')->first();if($application)$render('admin-progress','/admin/applications/'.$application->id.'/progress',$admin,fn($request)=>$progress->show($request,$application->id,app(App\Services\ApplicationStatusCatalog::class)));
    }else echo "READONLY_SKIP no-eligible-job-administrator\n";
    $chatAdmin=$admins->first(fn($user)=>$user->can('manage-application-chat')||$user->hasAnyRole(['Admin','Super Admin']));
    if($chatAdmin)$render('chat-inbox-schedule','/admin/site-chat',$chatAdmin,fn($request)=>app(App\Http\Controllers\Chat\SiteChatController::class)->inbox($request));else echo "READONLY_SKIP no-eligible-chat-administrator\n";
    $employer=App\Models\User::whereRaw('LOWER(user_type)=?',['employer'])->whereNotNull('employer_approved_at')->where('status',1)->get()->first(fn($user)=>$user->can('post-job'));
    if($employer)$render('employer-post-job','/jobs/create',$employer,fn($request)=>app(App\Http\Controllers\Employer\JobController::class)->create($request));else echo "READONLY_SKIP no-approved-employer\n";
    $applicant=App\Models\User::where('user_type',App\Enum\UserType::JOB_SEEKER)->first();
    if($applicant)$render('applicant-profile','/job_seeker/job-seeker-profile',$applicant,fn($request)=>app(App\Http\Controllers\JobSeeker\JobSeekerProfileController::class)->profile($request));
    if($admin)$render('job-locations','/admin/job-locations',$admin,fn($request)=>app(App\Http\Controllers\Admin\JobLocationOptionsController::class)->index($request));
    if($applicant){
        $render('applicant-find-jobs','/job_seeker/find-jobs',$applicant,fn($request)=>app(App\Http\Controllers\JobSeeker\JobApplicationController::class)->findJobs($request));
        $render('applicant-password','/job_seeker/jobseeker-change-password',$applicant,fn($request)=>app(App\Http\Controllers\JobSeeker\JobSeekerProfileSettingController::class)->changePassword());
        $render('support-inbox','/support-center',$applicant,fn($request)=>app(App\Http\Controllers\Support\SupportCenterController::class)->index($request,app(App\Services\SupportCenter::class)));
        $render('support-compose','/support-center/new',$applicant,fn($request)=>app(App\Http\Controllers\Support\SupportCenterController::class)->create($request,app(App\Services\SupportCenter::class)));
    }
    $studioAdmin=$admins->first(fn($u)=>$u->can('manage-email-studio')||$u->hasAnyRole(['Admin','Super Admin']));
    echo 'STUDIO_PREFLIGHT '.json_encode(['enabled'=>(bool)config('email_studio.enabled'),'tables'=>Illuminate\Support\Facades\Schema::hasTable('email_studio_templates') && Illuminate\Support\Facades\Schema::hasTable('email_studio_send_logs'),'permission'=>$studioAdmin?(bool)$studioAdmin->can('manage-email-studio'):false,'canonical'=>$studioAdmin?(bool)$studioAdmin->hasAnyRole(['Admin','Super Admin']):false])."\n";
    if($studioAdmin)$render('email-studio','/admin/email-studio',$studioAdmin,fn($request)=>app(App\Http\Controllers\Admin\EmailStudioController::class)->index($request));else echo "READONLY_SKIP no-email-administrator\n";
    echo 'SMTP_CONFIGURATION '.json_encode(['saved_profiles'=>Illuminate\Support\Facades\DB::table('email_studio_smtp_profiles')->count(),'default_profile'=>app(App\Services\StudioSmtpProfiles::class)->defaultId()!==null,'environment_host'=>(bool)config('mail.mailers.studio_smtp.host'),'environment_username'=>(bool)config('mail.mailers.studio_smtp.username'),'templates'=>Illuminate\Support\Facades\DB::table('email_studio_templates')->count()])."\n";
    $captcha=Illuminate\Support\Facades\DB::table('site_security_settings')->where('id',1)->first();
    echo 'HCAPTCHA_STATE '.json_encode(['sitekey_present'=>(bool)($captcha?->hcaptcha_sitekey?:config('services.hcaptcha.sitekey')),'secret_present'=>(bool)($captcha?->hcaptcha_secret_encrypted?:config('services.hcaptcha.secret')),'enabled'=>$captcha?(bool)$captcha->hcaptcha_enabled:(bool)(config('services.hcaptcha.sitekey')&&config('services.hcaptcha.secret'))])."\n";
    $db->rollBack();echo "PASS: authorized templates verified with read-only database and no customer writes\n";
} catch(Throwable $error){if($db && $db->transactionLevel()>0)$db->rollBack();echo 'READONLY_VIEW_FAILURE '.$phase.' '.get_class($error).' HTTP '.($error instanceof Symfony\Component\HttpKernel\Exception\HttpExceptionInterface?$error->getStatusCode():0).' '.basename($error->getFile()).':'.$error->getLine()."\n";exit(1);}
