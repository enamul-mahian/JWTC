<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\Job;
use App\Models\Dashboard\Company;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
class PortalOperationsTest extends TestCase
{
    private User $admin;private User $employer;private Company $company;
    protected function setUp(): void
    {
        parent::setUp();$this->artisan('migrate',['--force'=>true])->assertSuccessful();
        DB::table('job_categories')->insert(['id'=>806,'name'=>'Portal fixture','status'=>1]);
        DB::table('countries')->insert(['id'=>806,'name'=>'Fixture country','code'=>'ZZ']);
        DB::table('company_sizes')->insert(['id'=>806,'title'=>'Fixture size']);
        DB::table('industry_types')->insert(['id'=>806,'name'=>'Fixture industry']);
        DB::table('job_types')->insert(['id'=>806,'name'=>'Full Time','status'=>1]);
        DB::table('skills')->insert(['id'=>806,'skill_name'=>'Fixture skill','job_category_id'=>806]);
        $this->admin=$this->actor('admin','admin');$this->employer=$this->actor('employer','employer');
        $this->admin->givePermissionTo(Permission::findOrCreate('active-job','web'));
        $this->admin->givePermissionTo(Permission::findOrCreate('employer-list','web'));
        $this->employer->givePermissionTo(Permission::findOrCreate('post-job','web'));
        $this->employer->forceFill(['employer_approved_at'=>now()])->save();
        $this->company=Company::create(['user_id'=>$this->employer->id,'company_sizes_id'=>806,'industry_type_id'=>806,'country_id'=>806,'company_name'=>'Fixture company']);
    }
    private function actor(string $type,string $suffix): User
    {
        return User::create(['name'=>'Portal '.$suffix,'email'=>$suffix.'@example.test','phone'=>'9900'.($suffix==='admin'?'1':'2'),'password'=>'SyntheticPassword123!','user_type'=>$type,'status'=>1,'job_category_id'=>806]);
    }
    private function payload(): array
    {
        return ['title'=>'New fixture vacancy','vacancies'=>1,'job_category_id'=>806,'employment_status'=>[806],'application_deadline'=>'2027-12-01','resume_receiving_email'=>'jobs@example.test','job_level'=>['mid'],'job_context'=>'Summary','job_responsibilities'=>'Responsibilities','work_place'=>['work_at_office'],'country_id'=>806,'minimum_salary'=>20,'maximum_salary'=>30,'skills'=>[806],'mini_age'=>18,'company_id'=>$this->company->id,'salary_type'=>'hourly','benefits_text'=>"Pension\nTraining"];
    }
    private function job(): Job
    {
        return Job::create(['user_id'=>$this->employer->id,'company_id'=>$this->company->id,'job_category_id'=>806,'country_id'=>806,'title'=>'Existing fixture','slug'=>'historic-fixture-url','job_status'=>'Approved','salary_type'=>'monthly','resume_receiving_email'=>'old@example.test','minimum_salary'=>100,'maximum_salary'=>200,'job_responsibilities'=>'<p>Original</p>','skills'=>null,'is_publish'=>1]);
    }
    public function test_approved_employer_can_render_post_job_form_submit_and_reopen_for_edit(): void
    {
        $this->actingAs($this->employer)->get('/jobs/create')->assertOk()->assertSee('Submit job for review')->assertSee('Fixture skill')->assertDontSee('ckeditor.js')->assertDontSee('jquery-min.js');
        $data=$this->payload();$data['company_id']=99999;$data['is_publish']=1;
        $this->post('/jobs',$data)->assertRedirect()->assertSessionHasNoErrors();
        $job=Job::where('title',$data['title'])->firstOrFail();
        $this->assertSame($this->company->id,(int)$job->company_id);$this->assertSame(0,(int)$job->is_publish);
        $this->get('/jobs/'.$job->id.'/edit')->assertOk()->assertSee('Save changes for review')->assertSee('Training');
    }
    public function test_admin_edits_another_company_job_preserving_owner_slug_and_publication(): void
    {
        $job=$this->job();$this->actingAs($this->admin)->get('/admin/jobs/'.$job->id.'/edit')->assertOk()->assertSee('Save job');
        $data=$this->payload()+['is_publish'=>1];$data['user_id']=$this->admin->id;$data['company_id']=99999;$data['slug']='forged-slug';
        $this->patch('/admin/jobs/'.$job->id,$data)->assertRedirect()->assertSessionHasNoErrors();
        $fresh=$job->fresh();$this->assertSame('historic-fixture-url',$fresh->slug);$this->assertSame($this->employer->id,(int)$fresh->user_id);$this->assertSame($this->company->id,(int)$fresh->company_id);$this->assertSame(1,(int)$fresh->is_publish);
        $this->assertDatabaseHas('job_edit_events',['job_id'=>$job->id,'actor_id'=>$this->admin->id]);
        $this->actingAs($this->employer)->patch('/admin/jobs/'.$job->id,$data)->assertForbidden();
    }
    public function test_legacy_null_job_json_missing_job_and_authenticated_admin_pages_do_not_500(): void
    {
        $job=$this->job();$this->actingAs($this->admin);
        foreach(['/admin/jobs','/admin/active-job','/admin/inactive-job','/admin/application-statuses','/admin/applications','/admin/company-approvals'] as $path)$this->get($path)->assertOk();
        $this->get(route('job.show',$job->id))->assertOk();$this->get(route('job.show',999999))->assertNotFound();
        $this->get('/not-a-real-portal-page')->assertNotFound()->assertSee('Page not found');
    }
    public function test_admin_approved_progress_design_updates_the_same_applicant_timeline(): void
    {
        $job=$this->job();$application=DB::table('applications')->insertGetId(['user_id'=>$this->employer->id,'employer_id'=>$this->company->id,'job_id'=>$job->id,'status'=>1,'date_applied'=>now()->toDateString(),'expected_salary'=>20,'created_at'=>now(),'updated_at'=>now()]);
        $this->actingAs($this->admin)->get('/admin/applications/'.$application.'/progress')->assertOk()->assertSee('Application Timeline')->assertSee('Save progress update')->assertSee('jg-progress-circle',false);
        $this->patch('/admin/applications/'.$application.'/progress',['status'=>2,'note'=>'Fixture assessment'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('applications',['id'=>$application,'status'=>2]);$this->assertDatabaseHas('application_status_events',['application_id'=>$application,'note'=>'Fixture assessment']);
        $this->actingAs($this->employer)->get('/admin/applications/'.$application.'/progress')->assertForbidden();
    }
    public function test_canonical_admin_role_can_open_chat_and_presence_does_not_save_unsaved_hours(): void
    {
        Role::findOrCreate('Admin','web');$this->admin->assignRole('Admin');
        $this->actingAs($this->admin)->get('/admin/site-chat')->assertOk()->assertSee('Support timezone')->assertSee('Chat Inbox');
        $data=['enabled'=>1,'available'=>1,'schedule_enabled'=>1,'timezone'=>'Europe/London','hours'=>[1=>['start'=>'09:00','end'=>'17:00']]];
        $this->post('/admin/site-chat/settings',$data)->assertRedirect()->assertSessionHasNoErrors();
        $before=DB::table('site_chat_settings')->value('weekly_hours');
        $this->postJson('/admin/site-chat/presence',['available'=>1,'hours'=>[1=>['start'=>'01:00','end'=>'02:00']]])->assertOk();
        $this->assertSame($before,DB::table('site_chat_settings')->value('weekly_hours'));
        $data['hours'][1]['end']='';$this->post('/admin/site-chat/settings',$data)->assertSessionHasErrors('hours');
        $this->actingAs($this->employer)->get('/admin/site-chat')->assertForbidden();
    }
    public function test_native_legacy_forms_escape_values_and_emit_csrf_and_method_fields(): void
    {
        $form=app('native.form');$open=(string)$form->model((object)['name'=>'<script>unsafe</script>'],['route'=>['admin.jobs.update',1],'method'=>'patch']);
        $this->assertStringContainsString('name="_token"',$open);$this->assertStringContainsString('name="_method"',$open);
        $this->assertStringContainsString('&lt;script&gt;unsafe&lt;/script&gt;',(string)$form->text('name'));
        $form->close();$this->assertStringNotContainsString('unsafe',(string)$form->text('name'));
        $this->assertStringContainsString('selected',(string)$form->select('status',[1=>'Active'],1));
    }
}
