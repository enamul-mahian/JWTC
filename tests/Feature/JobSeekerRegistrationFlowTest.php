<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
class JobSeekerRegistrationFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();$this->artisan('migrate',['--force'=>true])->assertSuccessful();
        config(['services.hcaptcha.enabled'=>false,'security.registration_otp.enabled'=>false]);
        DB::table('job_categories')->insert(['id'=>805,'name'=>'Registration fixture','status'=>1]);
        DB::table('application_settings')->insert(['job_seeker_referral_code'=>'TEST-CODE-ONLY']);
        Role::findOrCreate('Job Seeker','web');
    }
    private function data(): array
    {
        return ['name'=>'Registration fixture','email'=>'registration-fixture@example.test','phone'=>'77000000005','password'=>'GoodPassword123!','password_confirmation'=>'GoodPassword123!','job_category_id'=>805,'referral_code'=>'TEST-CODE-ONLY'];
    }
    public function test_category_is_visible_and_required_without_email_otp(): void
    {
        $this->get('/register')->assertOk()->assertSee('Preferred Job Category')->assertSee('Registration fixture');
        $data=$this->data();unset($data['job_category_id']);
        $this->from('/register')->post('/register',$data)->assertRedirect('/register')->assertSessionHasErrors('job_category_id');
        $this->assertDatabaseMissing('users',['email'=>$data['email']]);
        $this->get('/register')->assertSee('Please check the following:')->assertSee('registration-fixture@example.test');
    }
    public function test_successful_registration_creates_category_role_profile_and_logs_in(): void
    {
        Event::fake([Registered::class]);
        $this->post('/register',$this->data())->assertRedirect(route('home'))->assertSessionHasNoErrors();
        $user=User::where('email',$this->data()['email'])->firstOrFail();
        $this->assertSame(805,(int)$user->job_category_id);$this->assertTrue($user->hasRole('Job Seeker'));
        $this->assertDatabaseHas('personal_information',['user_id'=>$user->id]);
        $this->assertAuthenticatedAs($user);Event::assertDispatched(Registered::class);
    }
    public function test_long_phone_and_bad_category_never_reach_database_insert(): void
    {
        $data=$this->data();$data['phone']=str_repeat('9',21);
        $this->post('/register',$data)->assertSessionHasErrors('phone');
        $data=$this->data();$data['job_category_id']=999999;
        $this->post('/register',$data)->assertSessionHasErrors('job_category_id');
        $this->assertDatabaseMissing('users',['email'=>$data['email']]);
    }
    public function test_missing_role_leaves_no_partial_account_and_allows_retry(): void
    {
        Role::where('name','Job Seeker')->delete();
        $this->post('/register',$this->data())->assertSessionHasErrors('registration');
        $this->assertDatabaseMissing('users',['email'=>$this->data()['email']]);
        Role::findOrCreate('Job Seeker','web');
        $this->post('/register',$this->data())->assertRedirect(route('home'));
        $this->assertDatabaseCount('users',1);
    }
    public function test_repeat_submission_after_logout_is_validation_error_and_never_duplicates_account(): void
    {
        $this->post('/register',$this->data())->assertRedirect(route('home'));
        Auth::logout();
        $this->post('/register',$this->data())->assertSessionHasErrors(['email','phone']);
        $this->assertDatabaseCount('users',1);
    }
}
