<?php
namespace App\Http\Controllers\Chat;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
class SiteChatController extends Controller
{
    private function settings(): ?object
    {
        return Schema::hasTable('site_chat_settings') ? DB::table('site_chat_settings')->where('id',1)->first() : null;
    }
    private function requireEnabled(): void { abort_unless($this->settings()?->enabled, 404); }
    private function reviewer(Request $request): void
    {
        abort_unless(in_array(strtolower((string)$request->user()?->user_type), ['admin','super_admin'], true), 403);
        abort_unless(($request->user()->can('manage-application-chat') || $request->user()->hasAnyRole(['Admin','Super Admin'])), 403);
    }
    private function owned(Request $request, string $id): object
    {
        abort_unless(hash_equals((string)$request->session()->get('site_chat_id', ''), $id) && $id !== '', 404);
        return DB::table('site_chat_threads')->where('public_id', $id)->first() ?? abort(404);
    }
    private function messages(object $thread, int $after = 0)
    {
        return DB::table('site_chat_messages')->where('thread_id',$thread->id)->where('id','>',$after)->orderBy('id')->limit(100)->get()->map(fn($row)=>[
            'id'=>(int)$row->id, 'from'=>$row->admin_id ? 'Support' : 'You', 'body'=>Crypt::decryptString($row->body_encrypted), 'created_at'=>\Illuminate\Support\Carbon::parse($row->created_at,config('app.timezone'))->toIso8601String(),
        ]);
    }
    public function state(Request $request)
    {
        $settings = $this->settings();
        return response()->json(['enabled'=>(bool)$settings?->enabled, 'online'=>(bool)($settings?->enabled && $settings?->online_until && now()->lt($settings->online_until) && app(\App\Services\ChatHours::class)->open($settings)), 'within_hours'=>$settings ? app(\App\Services\ChatHours::class)->open($settings) : false, 'thread'=>$request->session()->get('site_chat_id')])->header('Cache-Control','no-store, private');
    }
    public function start(Request $request)
    {
        $this->requireEnabled();
        $data=$request->validate(['name'=>'required|string|max:120','email'=>'required|email|max:254','body'=>'required|string|max:2000','website'=>'prohibited']);
        if ($existing=$request->session()->get('site_chat_id')) return $this->send($request, $existing);
        $thread=DB::transaction(function() use($data) {
            $id=DB::table('site_chat_threads')->insertGetId(['public_id'=>(string)Str::uuid(), 'name_encrypted'=>Crypt::encryptString(trim($data['name'])), 'email_encrypted'=>Crypt::encryptString($data['email']), 'status'=>'open', 'last_message_at'=>now(), 'created_at'=>now(), 'updated_at'=>now()]);
            DB::table('site_chat_messages')->insert(['thread_id'=>$id,'admin_id'=>null,'body_encrypted'=>Crypt::encryptString(trim($data['body'])),'created_at'=>now()]);
            return DB::table('site_chat_threads')->where('id',$id)->first();
        });
        $request->session()->put('site_chat_id',$thread->public_id);
        return response()->json(['thread'=>$thread->public_id,'messages'=>$this->messages($thread)],201)->header('Cache-Control','no-store, private');
    }
    public function poll(Request $request, string $id)
    {
        $this->requireEnabled(); $thread=$this->owned($request,$id);
        $data=$request->validate(['after'=>'sometimes|integer|min:0']);
        return response()->json(['messages'=>$this->messages($thread,(int)($data['after']??0)), 'status'=>$thread->status])->header('Cache-Control','no-store, private');
    }
    public function send(Request $request, string $id)
    {
        $this->requireEnabled(); $thread=$this->owned($request,$id);
        $data=$request->validate(['body'=>'required|string|max:2000']);
        DB::transaction(function() use($thread,$data) {
            $locked=DB::table('site_chat_threads')->where('id',$thread->id)->lockForUpdate()->first();
            abort_unless($locked?->status==='open',422,'This conversation is closed.');
            abort_if(DB::table('site_chat_messages')->where('thread_id',$thread->id)->whereNull('admin_id')->count()>=100,422,'This conversation has reached its message limit.');
            DB::table('site_chat_messages')->insert(['thread_id'=>$thread->id,'admin_id'=>null,'body_encrypted'=>Crypt::encryptString(trim($data['body'])),'created_at'=>now()]);
            DB::table('site_chat_threads')->where('id',$thread->id)->update(['last_message_at'=>now(),'updated_at'=>now()]);
        });
        return response()->json(['sent'=>true],201);
    }
    public function inbox(Request $request)
    {
        $this->reviewer($request); abort_unless($settings=$this->settings(),503);
        $selected=null; $messages=collect();
        if ($request->filled('thread')) {
            $data=$request->validate(['thread'=>'required|uuid']);
            $selected=DB::table('site_chat_threads')->where('public_id',$data['thread'])->firstOrFail();
            $messages=$this->messages($selected,(int)$request->integer('after',0));
            if ($request->expectsJson()) return response()->json(['messages'=>$messages,'status'=>$selected->status])->header('Cache-Control','no-store, private');
        }
        $threads=DB::table('site_chat_threads')->latest('last_message_at')->paginate(30);
        $threads->getCollection()->transform(function($row){$row->name=Crypt::decryptString($row->name_encrypted);$row->email=Crypt::decryptString($row->email_encrypted);return $row;});
        return response()->view('site-chat.inbox',array_merge(compact('threads','selected','messages','settings'), ['setting'=>gs()]))->header('Cache-Control','no-store, private');
    }
    public function reply(Request $request, string $id)
    {
        $this->reviewer($request);
        $data=$request->validate(['body'=>'required|string|max:2000']);
        DB::transaction(function() use($request,$id,$data) {
            $thread=DB::table('site_chat_threads')->where('public_id',$id)->lockForUpdate()->first(); abort_unless($thread,404);
            abort_unless($thread->status==='open',422);
            DB::table('site_chat_messages')->insert(['thread_id'=>$thread->id,'admin_id'=>$request->user()->id,'body_encrypted'=>Crypt::encryptString(trim($data['body'])),'created_at'=>now()]);
            DB::table('site_chat_threads')->where('id',$thread->id)->update(['last_message_at'=>now(),'updated_at'=>now()]);
        });
        return back()->with('success','Reply saved.');
    }
    public function configure(Request $request)
    {
        $this->reviewer($request); abort_unless($this->settings(),503);
        $data=$request->validate([
            'enabled'=>'required|boolean',
            'available'=>'required|boolean',
            'schedule_enabled'=>'sometimes|boolean',
            'timezone'=>'sometimes|timezone',
            'hours'=>'sometimes|array|max:7',
            'hours.*'=>'array',
            'hours.*.closed'=>'sometimes|boolean',
            'hours.*.start'=>'nullable|date_format:H:i',
            'hours.*.end'=>'nullable|date_format:H:i',
        ]);
        $update=[
            'enabled'=>(bool)$data['enabled'],
            'online_until'=>$data['enabled'] && $data['available'] ? now()->addSeconds(90) : null,
            'updated_at'=>now(),
        ];
        if ($request->has('schedule_enabled')) {
            $hours=[];
            foreach ($data['hours']??[] as $day=>$range) {
                if (!in_array((string)$day, ['1','2','3','4','5','6','7'], true)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'hours'=>'Use Monday through Sunday only.',
                    ]);
                }
                $start=$range['start']??null;
                $end=$range['end']??null;
                // Explicit Closed days, empty hours and 00:00–00:00 all
                // represent closure. Never treat equal times as 24-hour open.
                if (!empty($range['closed']) || (!$start && !$end)
                    || ($start==='00:00' && $end==='00:00')) {
                    continue;
                }
                if (!$start || !$end || $start===$end) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'hours.'.$day.'.start'=>'Choose distinct opening and closing times, or mark the day Closed.',
                    ]);
                }
                $hours[$day]=['start'=>$start,'end'=>$end];
            }
            // An entirely closed week is valid. The widget remains offline
            // when schedule limits apply, while visitor messages still work.
            $update += [
                'schedule_enabled'=>(bool)$data['schedule_enabled'],
                'timezone'=>$data['timezone']??'UTC',
                'weekly_hours'=>json_encode($hours,JSON_THROW_ON_ERROR),
            ];
        }
        DB::table('site_chat_settings')->where('id',1)->update($update);
        return $request->expectsJson()?response()->json(['saved'=>true]):back()->with('success','Chat settings saved.');
    }
    public function presence(Request $request)
    {
        $this->reviewer($request);$settings=$this->settings();abort_unless($settings,503);
        $data=$request->validate(['available'=>'required|boolean']);
        DB::table('site_chat_settings')->where('id',1)->update(['online_until'=>$settings->enabled && $data['available'] ? now()->addSeconds(90):null]);
        return response()->json(['saved'=>true]);
    }

}
