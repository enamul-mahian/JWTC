<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class ConfigureHcaptcha {
 public function handle(Request $request,Closure $next){
  if(Schema::hasTable('site_security_settings')){
   $setting=DB::table('site_security_settings')->where('id',1)->first();
   if($setting){config(['services.hcaptcha.enabled'=>(bool)$setting->hcaptcha_enabled,'services.hcaptcha.sitekey'=>$setting->hcaptcha_sitekey]);if($setting->hcaptcha_secret_encrypted)config(['services.hcaptcha.secret'=>decrypt($setting->hcaptcha_secret_encrypted)]);}
   elseif(config('services.hcaptcha.sitekey')&&config('services.hcaptcha.secret'))config(['services.hcaptcha.enabled'=>true]);
  }
  return $next($request);
 }
}
