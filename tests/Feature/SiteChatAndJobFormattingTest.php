<?php
namespace Tests\Feature;
use App\Services\JobContent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
class SiteChatAndJobFormattingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate',['--force'=>true])->assertSuccessful();
        DB::table('job_categories')->insert(['id'=>802,'name'=>'Chat fixture','status'=>1]);
    }
    private function actor(string $type): User
    {
        static $i=0; $i++;
        return User::create(['name'=>'Fixture','email'=>'chat'.$i.'@example.test','phone'=>'555'.str_pad((string)$i,8,'0',STR_PAD_LEFT),'password'=>'SyntheticPassword123!', 'user_type'=>$type,'status'=>1,'job_category_id'=>802]);
    }
    public function test_rich_job_lists_and_json_benefits_render_without_active_content(): void
    {
        $content=app(JobContent::class);
        $html=$content->render('<p>First paragraph</p><ul><li>One</li><li onclick="alert(1)">Two</li></ul><script>alert(1)</script><a href="javascript:alert(1)">Unsafe link</a><img src=x onerror=alert(1)>');
        $this->assertStringContainsString('<ul><li>One</li><li>Two</li></ul>',$html);
        $this->assertStringContainsString('<p>First paragraph</p>',$html);
        foreach (['script','onclick','onerror','javascript:','<img'] as $bad) $this->assertStringNotContainsString($bad,$html);
        $benefits=$content->render('["mobile_bill","pension_policy","performance_bonus"]');
        $this->assertSame('<ul><li>Mobile Bill</li><li>Pension Policy</li><li>Performance Bonus</li></ul>',$benefits);
        $this->assertSame("First\n• One\n• Two",$content->text('<p>First</p><ul><li>One</li><li>Two</li></ul>'));
        $hidden=$content->render('<p>poster&#64;example.test</p><a href="mailto:poster%40example.test">Email</a>',['poster@example.test']);
        $this->assertStringNotContainsString('poster',$hidden);
        $this->assertStringNotContainsString('mailto:',$hidden);
    }
    public function test_employment_type_ids_are_readable_and_polling_does_not_use_message_rate_allowance(): void
    {
        DB::table('job_types')->insert(['id'=>701,'name'=>'Full Time','status'=>1]);
        $job=new \App\Models\Job(['employment_status'=>'[701,"remote"]']);
        $this->assertSame('["Full Time","remote"]',$job->publicEmploymentLabels());
        for($i=0;$i<6;$i++) $this->getJson('/site-chat/state')->assertOk();
        $this->postJson('/site-chat/threads',['name'=>'Fixture','email'=>'fixture@example.test','body'=>'Hello'])->assertCreated();
    }
    public function test_visitor_chat_is_private_encrypted_and_session_bound(): void
    {
        $this->postJson('/site-chat/threads',['name'=>'Visitor','email'=>'guest@example.test','body'=>'Private hello'])->assertCreated();
        $id=DB::table('site_chat_threads')->value('public_id');
        $thread=DB::table('site_chat_threads')->first();
        $this->assertNotSame('guest@example.test',$thread->email_encrypted);
        $this->assertSame('guest@example.test',Crypt::decryptString($thread->email_encrypted));
        $this->assertNotSame('Private hello',DB::table('site_chat_messages')->value('body_encrypted'));
        $this->getJson('/site-chat/threads/'.$id)->assertOk()->assertJsonPath('messages.0.body','Private hello');
        $this->getJson('/site-chat/threads/00000000-0000-4000-8000-000000000000')->assertNotFound();
        $this->flushSession();
        $this->getJson('/site-chat/threads/'.$id)->assertNotFound();
        $this->postJson('/site-chat/threads/'.$id.'/messages',['body'=>'Cross-session message'])->assertNotFound();
        $this->assertSame(1,DB::table('site_chat_messages')->count());
    }
    public function test_admin_can_reply_and_control_visibility_and_presence_but_applicant_cannot(): void
    {
        $this->postJson('/site-chat/threads',['name'=>'Visitor','email'=>'offline@example.test','body'=>'Offline request'])->assertCreated();
        $id=DB::table('site_chat_threads')->value('public_id');
        $this->getJson('/site-chat/state')->assertJsonPath('online',false);
        $seeker=$this->actor('job_seeker');
        $this->actingAs($seeker)->get('/admin/site-chat')->assertForbidden();
        $this->postJson('/admin/site-chat/settings',['enabled'=>false,'available'=>true])->assertForbidden();
        $admin=$this->actor('admin');
        Permission::findOrCreate('manage-application-chat','web');$admin->givePermissionTo('manage-application-chat');
        $this->actingAs($admin)->get('/admin/site-chat')->assertOk()->assertSee('offline@example.test');
        $this->post('/admin/site-chat/'.$id.'/reply',['body'=>'Our reply'])->assertRedirect();
        $this->getJson('/site-chat/threads/'.$id)->assertJsonPath('messages.1.body','Our reply')->assertJsonPath('messages.1.from','Support');
        $this->postJson('/admin/site-chat/settings',['enabled'=>true,'available'=>true])->assertOk();
        $this->getJson('/site-chat/state')->assertJsonPath('online',true);
        $this->travel(91)->seconds();
        $this->getJson('/site-chat/state')->assertJsonPath('online',false);
        $this->postJson('/admin/site-chat/settings',['enabled'=>false,'available'=>false])->assertOk();
        $this->getJson('/site-chat/state')->assertJsonPath('enabled',false);
        $this->postJson('/site-chat/threads/'.$id.'/messages',['body'=>'Disabled'])->assertNotFound();
        $this->getJson('/site-chat/threads/'.$id)->assertNotFound();
    }
    public function test_widget_has_no_third_party_chat_embed_and_rate_limited_validation_is_enforced(): void
    {
        $this->get('/company/login')->assertOk()->assertSee('site-chat/widget.js')->assertDontSee('tawk.to')->assertDontSee('Tawk_API');
        $this->postJson('/site-chat/threads',['name'=>'Spam','email'=>'invalid','body'=>'Hi'])->assertUnprocessable();
        $this->postJson('/site-chat/threads',['name'=>'Spam','email'=>'test@example.test','body'=>'Hi','website'=>'bot'])->assertUnprocessable();
        $this->assertSame(0,DB::table('site_chat_threads')->count());
    }
}
