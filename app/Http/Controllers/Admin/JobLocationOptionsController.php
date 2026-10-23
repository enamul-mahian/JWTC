<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class JobLocationOptionsController extends Controller {
 private function admin(Request $r): void {abort_unless(in_array(strtolower((string)$r->user()?->user_type),['admin','super_admin'],true)&&$r->user()->can('active-job'),403);}
 public function index(Request $r){$this->admin($r);return view('admin.job.locations',['setting'=>gs(),'locations'=>DB::table('job_location_options')->join('countries','countries.id','=','job_location_options.country_id')->where('enabled',true)->orderBy('countries.name')->get(['countries.name','job_location_options.id'])]);}
 public function store(Request $r){$this->admin($r);$d=$r->validate(['name'=>'required|string|max:100','code'=>'nullable|string|max:3']);DB::transaction(function()use($d){$id=DB::table('countries')->where('name',$d['name'])->value('id');if(!$id)$id=DB::table('countries')->insertGetId(['name'=>$d['name'],'code'=>$d['code']??'ZZ']);DB::table('job_location_options')->updateOrInsert(['country_id'=>$id],['enabled'=>true,'updated_at'=>now(),'created_at'=>now()]);});return back()->with('success','Job location added.');}
 public function destroy(Request $r,int $id){$this->admin($r);DB::table('job_location_options')->where('id',$id)->update(['enabled'=>false,'updated_at'=>now()]);return back()->with('success','Location removed from new job choices. Historic jobs are preserved.');}
}
