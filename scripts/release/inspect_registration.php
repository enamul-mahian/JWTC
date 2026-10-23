<?php
// Read-only, privacy-filtered registration diagnostics. Never emit raw logs,
// SQL, credentials, email addresses, registration codes or customer records.
ini_set('display_errors','0');ini_set('log_errors','0');
try {
    $root=realpath('.');require $root.'/vendor/autoload.php';
    $app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo 'OTP_ENABLED '.(config('security.registration_otp.enabled')?'yes':'no')."\n";
    echo 'SMTP_CONFIGURED '.(config('mail.mailers.smtp.host') && config('mail.from.address')?'yes':'no')."\n";
    $db=Illuminate\Support\Facades\DB::connection();
    foreach($db->select('SHOW COLUMNS FROM users') as $column) if(in_array($column->Field,['job_category_id','phone','user_type'],true)) echo 'USER_COLUMN '.json_encode(['name'=>$column->Field,'nullable'=>$column->Null==='YES','has_default'=>$column->Default!==null])."\n";
    foreach(['Job Seeker','Employer'] as $name) echo 'ROLE_PRESENT '.json_encode(['role'=>$name,'present'=>Illuminate\Support\Facades\DB::table('roles')->where('name',$name)->where('guard_name','web')->exists()])."\n";
    $files=glob($root.'/storage/logs/laravel*.log');usort($files,fn($a,$b)=>filemtime($b)<=>filemtime($a));
    foreach(array_slice($files,0,2) as $file) {
        $process=new Symfony\Component\Process\Process(['/usr/bin/tail','-n','1800',$file]);$process->setTimeout(20);$process->mustRun();
        $lines=explode("\n",$process->getOutput());$events=[];
        foreach($lines as $line) {
            if(str_contains($line,'.ERROR:')) {
                $event=['registration'=>str_contains($line,'RegisteredUserController'),'sqlstate'=>null,'category'=>'other'];
                if(preg_match('/SQLSTATE\[([A-Z0-9]{5})\]/',$line,$match))$event['sqlstate']=$match[1];
                foreach(['job_category_id','personal_information','Route [','RoleDoesNotExist','PermissionDoesNotExist','doesn\'t have a default value','cannot be null','Duplicate entry','Data too long','Mailer','SMTP','Unique constraint','Class "','Undefined'] as $category)if(str_contains($line,$category))$event['category']=$category;
                $events[]=$event;
            } elseif($events && preg_match('#/(app/Http/Controllers/Auth/RegisteredUserController.php|app/Services/RegistrationOtpService.php|app/Http/Middleware/[A-Za-z]+.php)\((\d+)\)#',$line,$match)) {
                $index=array_key_last($events);$events[$index]['frames'][]=$match[1].':'.$match[2];
                if(str_contains($match[1],'RegisteredUserController'))$events[$index]['registration']=true;
            }
        }
        foreach(array_slice($events,-12) as $event) echo 'SAFE_ERROR '.json_encode($event)."\n";
    }
    echo "PASS: read-only filtered diagnostics complete\n";
} catch(Throwable $error) {echo 'SAFE_DIAGNOSTIC_FAILURE '.get_class($error)."\n";exit(1);}
