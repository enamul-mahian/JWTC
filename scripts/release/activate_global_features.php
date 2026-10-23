<?php
ini_set('display_errors','0');ini_set('log_errors','0');
try {
 $home=realpath(getenv('HOME'));$root=realpath('.');if($root!==$home.'/joingallagher.uk'||is_link($root))throw new RuntimeException('Root');
 require $root.'/vendor/autoload.php';$file=$root.'/.env';$before=file_get_contents($file);$old=Dotenv\Dotenv::parse($before);$next=$before;
 foreach(['EMAIL_STUDIO_ENABLED','SUPPORT_CENTER_ENABLED'] as $key){$count=0;$next=preg_replace('/^'.preg_quote($key,'/').'\s*=.*$/m',$key.'=true',$next,-1,$count);if(!$count)$next.="\n".$key."=true\n";}
 $parsed=Dotenv\Dotenv::parse($next);foreach($old as $k=>$v)if(!in_array($k,['EMAIL_STUDIO_ENABLED','SUPPORT_CENTER_ENABLED'],true)&&($parsed[$k]??null)!==$v)throw new RuntimeException('Unrelated configuration change');
 if(!hash_equals($old['APP_KEY'],$parsed['APP_KEY']))throw new RuntimeException('Key changed');
 if($next!==$before){$parent=$home.'/jwtc-private-deployments';if(is_link($parent)||realpath($parent)!==$parent)throw new RuntimeException('Unsafe private parent');$dir=$parent.'/global-feature-activation-'.gmdate('YmdHis').'-'.bin2hex(random_bytes(4));mkdir($dir,0700);file_put_contents($dir.'/.env.before',$before,LOCK_EX);chmod($dir.'/.env.before',0600);$tmp=$root.'/.env.global-'.bin2hex(random_bytes(4));file_put_contents($tmp,$next,LOCK_EX);chmod($tmp,0600);if(!rename($tmp,$file))throw new RuntimeException('Cannot activate');}
 $process=new Symfony\Component\Process\Process([PHP_BINARY,'artisan','config:clear'],$root);$process->setTimeout(60);$process->run();if(!$process->isSuccessful())throw new RuntimeException('Cache clear');
 $verify=new Symfony\Component\Process\Process([PHP_BINARY,'-r','require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); echo json_encode([(bool)config("email_studio.enabled"),(bool)config("support_center.enabled")]);'],$root);$verify->setTimeout(60);$verify->run();if(!$verify->isSuccessful()||json_decode(trim($verify->getOutput()),true)!==[true,true])throw new RuntimeException('Activation verification');
 echo "ACTIVATED_FEATURES email_studio support_center; original APP_KEY, database and SMTP configuration preserved\n";
} catch(Throwable $e){echo 'FAIL: requested module activation stopped; private details suppressed '.get_class($e)."\n";exit(1);}
