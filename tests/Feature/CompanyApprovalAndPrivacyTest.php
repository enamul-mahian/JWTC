<?php
namespace Tests\Feature;
use App\Enum\UserType;
use App\Models\User;
use App\Models\Dashboard\Company;
use App\Services\PosterEmailVisibility;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
class CompanyApprovalAndPrivacyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        config(['services.hcaptcha.enabled' => false, 'security.registration_otp.enabled' => false]);
        DB::table('job_categories')->insert(['id' => 801, 'name' => 'Test industry', 'status' => 1]);
        Role::findOrCreate('Employer', 'web');
    }
    private function account(string $type, bool $approved = false): User
    {
        static $counter = 0; $counter++;
        $user = User::create(['name' => 'Fixture '.$counter, 'email' => 'fixture'.$counter.'@example.test', 'phone' => '700000'.str_pad((string)$counter, 5, '0', STR_PAD_LEFT), 'password' => Hash::make('GoodPassword123!'), 'user_type' => $type, 'status' => 1, 'job_category_id' => 801]);
        if ($approved) $user->forceFill(['employer_approved_at' => now()])->save();
        return $user;
    }
    public function test_active_status_alone_does_not_bypass_approval_in_web_or_existing_api_session(): void
    {
        $company = $this->account(UserType::EMPLOYER);
        $this->actingAs($company)->get('/employer/dashboard')->assertRedirect(route('company.approval'));
        $this->get('/profile')->assertRedirect(route('company.approval'));
        $this->get('/company/approval')->assertOk();
        Sanctum::actingAs($company);
        $this->getJson('/api/employer/jobs')->assertForbidden()->assertJsonPath('approval_required', true);
        $this->postJson('/api/employer/job/create', ['title' => 'Bypass'])->assertForbidden();
        $this->assertDatabaseMissing('jobs', ['title' => 'Bypass']);
    }
    public function test_company_api_registration_ignores_privileged_fields_and_issues_no_token(): void
    {
        $this->postJson('/api/registration', ['name' => 'New company', 'email' => 'new-company@example.test', 'phone' => '770000001', 'job_categories_id' => 801, 'password' => 'GoodPassword123!', 'password_confirmation' => 'GoodPassword123!', 'user_type' => 'super_admin', 'status' => 1, 'employer_approved_at' => now()->toDateTimeString()])
            ->assertCreated()->assertJsonPath('approval_required', true)->assertJsonPath('token', null);
        $new = User::where('email', 'new-company@example.test')->firstOrFail();
        $this->assertSame(UserType::EMPLOYER, $new->user_type);
        $this->assertNull($new->employer_approved_at);
        $this->assertSame(0, (int)$new->status);
        $this->assertSame(0, $new->tokens()->count());
        $this->postJson('/api/login', ['email' => $new->email, 'password' => 'GoodPassword123!'])->assertForbidden();
    }
    public function test_separate_login_panels_reject_cross_account_credentials(): void
    {
        $company = $this->account(UserType::EMPLOYER, true);
        $this->post('/jobseeker/login', ['email' => $company->email, 'password' => 'GoodPassword123!'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $seeker = $this->account(UserType::JOB_SEEKER);
        $this->post('/company/login', ['email' => $seeker->email, 'password' => 'GoodPassword123!'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/company/login', ['email' => $company->email, 'password' => 'GoodPassword123!'])->assertRedirect(route('employer.dashboard'));
        $this->assertAuthenticatedAs($company);
    }
    public function test_pending_company_can_only_reach_holding_page_and_logout_after_login(): void
    {
        $company = $this->account(UserType::EMPLOYER);
        $this->post('/company/login', ['email' => $company->email, 'password' => 'GoodPassword123!'])->assertRedirect(route('company.approval'));
        $this->get('/company/approval')->assertSee('Awaiting administrator approval');
        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }
    public function test_administrator_approval_and_revocation_are_audited_and_revoke_tokens(): void
    {
        $admin = $this->account(UserType::ADMIN);
        $admin->givePermissionTo(Permission::findOrCreate('employer-list', 'web'));
        $company = $this->account(UserType::EMPLOYER);
        $this->actingAs($admin)->post('/admin/company-approvals/'.$company->id, ['decision' => 'approve', 'reason' => 'Reviewed'])->assertRedirect();
        $this->assertNotNull($company->fresh()->employer_approved_at);
        $company->createToken('existing');
        $this->post('/admin/company-approvals/'.$company->id, ['decision' => 'revoke'])->assertRedirect();
        $this->assertNull($company->fresh()->employer_approved_at);
        $this->assertSame(0, $company->tokens()->count());
        $this->assertDatabaseCount('employer_review_events', 2);
        $this->actingAs($company->fresh())->get('/employer/dashboard')->assertRedirect(route('company.approval'));
    }
    public function test_company_contact_email_serialization_is_controlled_by_admin_setting(): void
    {
        $company = new Company(['company_name' => 'Privacy fixture', 'contact_per_email' => 'poster-private@example.test']);
        $settings = DB::table('application_settings');
        // Create fixture from the schema's nullable/default columns only.
        $settings->insert(['show_poster_email' => false]);
        $job = new \App\Models\Job(['resume_receiving_email' => 'poster-private@example.test', 'job_context' => 'Email poster-private@example.test to apply']);
        $job->setRelation('company', $company);
        $this->assertArrayNotHasKey('resume_receiving_email', $job->toArray());
        $this->assertStringNotContainsString('poster-private@example.test', $job->toArray()['job_context']);
        $this->assertNull($job->publicPosterEmail());
        $this->assertArrayNotHasKey('contact_per_email', $company->toArray());
        $settings->update(['show_poster_email' => true]);
        app()->forgetInstance(PosterEmailVisibility::class);
        $this->assertSame('poster-private@example.test', $company->toArray()['contact_per_email']);
        $this->assertSame('poster-private@example.test', $job->publicPosterEmail());
    }
    public function test_public_job_and_portal_routes_exist_without_authentication(): void
    {
        $this->get('/jobseeker/login')->assertOk()->assertSee('Job Seeker Sign In');
        $this->get('/company/login')->assertOk()->assertSee('Company Sign In');
        $this->get('/company/register')->assertOk()->assertSee('administrator approval');
        $this->get('/search-jobs')->assertOk()->assertSee('Latest Job Vacancies');
        $this->get('/vacancies/99999999')->assertNotFound();
    }
}
