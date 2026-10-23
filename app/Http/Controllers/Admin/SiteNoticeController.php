<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\SiteNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SiteNoticeController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless(in_array(strtolower((string)$request->user()?->user_type),['admin','super_admin'],true),403);
        abort_unless($request->user()->can('application-setting'),403);
    }
    public function index(Request $request, SiteNotice $notices)
    {
        $this->authorizeAdmin($request);
        $notice=$notices->current(); abort_unless($notice,503);
        return response()->view('admin.setting.news_ticker',['notice'=>$notice,'setting'=>gs()])->header('Cache-Control','private, no-store');
    }
    public function update(Request $request, SiteNotice $notices)
    {
        $this->authorizeAdmin($request); abort_unless($notices->current(),503);
        $data=$request->validate([
            'enabled'=>'required|boolean','speed'=>'required|integer|between:15,160',
            'items'=>'required|array|max:10','items.*.title'=>'nullable|string|max:200',
            'items.*.url'=>['nullable','string','max:2048',function($attribute,$value,$fail){if(!SiteNotice::safeUrl($value))$fail('Use a site path starting with / or a valid https:// link.');}],
        ]);
        $items=[];
        foreach($data['items'] as $item) {
            if (trim($item['title']??'')==='') continue;
            $items[]=['title'=>trim($item['title']),'url'=>$item['url']??null];
        }
        if($data['enabled'] && !$items) return back()->withErrors(['items'=>'Add at least one headline before enabling the ticker.'])->withInput();
        DB::table('site_notice_settings')->where('id',1)->update(['enabled'=>(bool)$data['enabled'],'speed'=>(int)$data['speed'],'items'=>json_encode($items,JSON_THROW_ON_ERROR),'updated_by'=>$request->user()->id,'updated_at'=>now()]);
        return back()->with('success','News ticker saved.');
    }
}
