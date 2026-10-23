<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SecuritySettingsController extends Controller {
 private function admin(Request $r): void {abort_unless(in_array(strtolower((string)$r->user()?->user_type),['admin','super_admin'],true)&&$r->user()->can('application-setting'),403);}
 public function index(Request $r){$this->admin($r);return response()->view('admin.setting.security',['setting'=>gs(),'sitekey'=>config('services.hcaptcha.sitekey'),'configured'=>(bool)config('services.hcaptcha.secret'),'enabled'=>(bool)config('services.hcaptcha.enabled')])->header('Cache-Control','private, no-store');}
 public function update(Request $r){$this->admin($r);$d=$r->validate(['enabled'=>'nullable|boolean','sitekey'=>'required|uuid','hcaptcha_secret'=>'nullable|string|max:255']);$old=DB::table('site_security_settings')->where('id',1)->first();$secret=$d['hcaptcha_secret']??null;
  $encrypted=$secret?encrypt($secret):($old?->hcaptcha_secret_encrypted??(config('services.hcaptcha.secret')?encrypt(config('services.hcaptcha.secret')):null));
  if($r->boolean('enabled')&&!$encrypted)throw \Illuminate\Validation\ValidationException::withMessages(['hcaptcha_secret'=>'Enter the hCaptcha secret key before enabling protection.']);
  DB::table('site_security_settings')->updateOrInsert(['id'=>1],['hcaptcha_enabled'=>$r->boolean('enabled'),'hcaptcha_sitekey'=>$d['sitekey'],'hcaptcha_secret_encrypted'=>$encrypted,'updated_at'=>now(),'created_at'=>$old?->created_at??now()]);return back()->with('success','Human verification settings saved.');
 }
}
