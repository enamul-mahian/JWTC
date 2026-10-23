<?php
namespace Tests\Feature;
use App\Models\User;
use App\Services\SiteNotice;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
class NewsTickerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp(); $this->artisan('migrate',['--force'=>true])->assertSuccessful();
        DB::table('job_categories')->insert(['id'=>803,'name'=>'Ticker fixture','status'=>1]);
    }
    private function actor(string $type, bool $permission=true): User
    {
        static $n=0;$n++;
        $user=User::create(['name'=>'Ticker fixture','email'=>'ticker'.$n.'@example.test','phone'=>'888'.str_pad((string)$n,8,'0',STR_PAD_LEFT),'password'=>'SyntheticPassword123!','user_type'=>$type,'status'=>1,'job_category_id'=>803]);
        if($permission){Permission::findOrCreate('application-setting','web');$user->givePermissionTo('application-setting');}
        return $user;
    }
    private function data(int $speed=60): array
    {
        return ['enabled'=>true,'speed'=>$speed,'items'=>[['title'=>'Application update','url'=>'/search-jobs'],['title'=>'Second update','url'=>'https://example.test/news']]];
    }
    public function test_only_settings_authorized_administrators_can_edit_ticker(): void
    {
        $seeker=$this->actor('job_seeker');
        $this->actingAs($seeker)->get('/admin/news-ticker')->assertForbidden();
        $this->post('/admin/news-ticker',$this->data())->assertForbidden();
        $admin=$this->actor('admin',false);
        $this->actingAs($admin)->get('/admin/news-ticker')->assertForbidden();
        $manager=$this->actor('admin');
        $this->actingAs($manager)->get('/admin/news-ticker')->assertOk()->assertSee('Scrolling speed');
        $this->post('/admin/news-ticker',$this->data(110))->assertRedirect()->assertSessionHasNoErrors();
        $notice=app(SiteNotice::class)->current();
        $this->assertSame(110,(int)$notice->speed);
        $this->assertSame('Application update',$notice->items[0]['title']);
        $this->assertSame((int)$manager->id,(int)$notice->updated_by);
    }
    public function test_invalid_speed_and_unsafe_links_do_not_change_saved_settings(): void
    {
        $this->actingAs($this->actor('admin'));
        $original=DB::table('site_notice_settings')->first();
        foreach([14,161] as $speed)$this->post('/admin/news-ticker',$this->data($speed))->assertSessionHasErrors('speed');
        foreach(['javascript:alert(1)','//evil.test','/\\evil.test','data:text/html,test'] as $url){$data=$this->data();$data['items'][0]['url']=$url;$this->post('/admin/news-ticker',$data)->assertSessionHasErrors('items.0.url');}
        $this->assertEquals($original,DB::table('site_notice_settings')->first());
    }
    public function test_public_strip_escapes_headlines_and_respects_visibility(): void
    {
        $items=[['title'=>'<script>alert(1)</script>','url'=>'/search-jobs']];
        DB::table('site_notice_settings')->where('id',1)->update(['items'=>json_encode($items),'speed'=>35]);
        $this->get('/company/login')->assertOk()->assertSee('data-speed="35"',false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;',false)->assertDontSee('<script>alert(1)</script>',false)->assertSee('Pause news ticker')->assertSee('theme/portal-motion.js')->assertDontSee('tawk.to');
        DB::table('site_notice_settings')->where('id',1)->update(['enabled'=>false]);
        $this->get('/company/login')->assertOk()->assertDontSee('data-news-ticker',false);
    }
    public function test_empty_enabled_ticker_is_rejected_and_empty_disabled_ticker_is_saved(): void
    {
        $this->actingAs($this->actor('admin'));
        $this->post('/admin/news-ticker',['enabled'=>true,'speed'=>60,'items'=>[['title'=>'','url'=>'']]])->assertSessionHasErrors('items');
        $this->post('/admin/news-ticker',['enabled'=>false,'speed'=>60,'items'=>[['title'=>'','url'=>'']]])->assertSessionHasNoErrors();
        $notice=app(SiteNotice::class)->current();$this->assertFalse((bool)$notice->enabled);$this->assertSame([],$notice->items);
        $this->assertTrue(SiteNotice::safeUrl('/search-jobs?title=Legal%20Assistant'));
        $this->assertTrue(SiteNotice::safeUrl('https://example.test/notice'));
    }
}
