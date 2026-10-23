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
        DB::table('job_location_options')->insert(['country_id'=>806,'enabled'=>true]);
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
        return User::create(['name'=>'Portal '.$suffix,'email'=>$suffix.'@example.test','phone'=>'99'.sprintf('%010u',crc32($suffix)),'password'=>'SyntheticPassword123!','user_type'=>$type,'status'=>1,'job_category_id'=>806]);
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

    public function test_new_editor_uses_checkbox_choices_rich_text_and_accepts_logo_and_faq(): void
    {
        $this->actingAs($this->employer)->get('/jobs/create')->assertOk()->assertSee('type="checkbox"',false)->assertSee('contenteditable="true"',false)->assertDontSee('multiple required',false)->assertSee('Job / company logo');
        $data=$this->payload()+['logo'=>\Illuminate\Http\UploadedFile::fake()->image('logo.png',80,80),'faq_question'=>['Can I apply?'],'faq_answer'=>['Apply through your account. jobs@example.test']];
        $this->post('/jobs',$data)->assertRedirect()->assertSessionHasNoErrors();
        $job=Job::where('title',$data['title'])->firstOrFail();$this->assertStringContainsString('job-logos/',$job->logo_path);
        $this->assertFileExists(public_path('assets/images/'.$job->logo_path));
        $this->assertSame('Can I apply?',json_decode($job->faqs,true)[0]['question']);
        $this->assertStringNotContainsString('jobs@example.test',$job->toArray()['faqs']);
        $this->assertSame($this->employer->id,(int)$job->user_id);
        @unlink(public_path('assets/images/'.$job->logo_path));
        $data['logo']=\Illuminate\Http\UploadedFile::fake()->create('unsafe.svg',1,'image/svg+xml');
        $this->post('/jobs',$data)->assertSessionHasErrors('logo');
    }
    public function test_job_location_choices_can_be_added_removed_without_deleting_country_or_jobs(): void
    {
        $job=$this->job();$this->actingAs($this->admin)->get('/admin/job-locations')->assertOk()->assertSee('Scotland')->assertSee('UK Territories');
        $this->post('/admin/job-locations',['name'=>'Jersey','code'=>'JE'])->assertRedirect();
        $id=DB::table('countries')->where('name','Jersey')->value('id');$option=DB::table('job_location_options')->where('country_id',$id)->value('id');
        $this->delete('/admin/job-locations/'.$option)->assertRedirect();
        $this->assertDatabaseHas('countries',['id'=>$id]);$this->assertDatabaseHas('jobs',['id'=>$job->id]);
        $this->assertSame(0,(int)DB::table('job_location_options')->where('id',$option)->value('enabled'));
        $this->actingAs($this->employer)->post('/admin/job-locations',['name'=>'Unauthorized'])->assertForbidden();
    }
    public function test_profile_handles_missing_personal_record_and_saves_private_unique_passport_with_lock(): void
    {
        $applicant=$this->actor('job_seeker','applicant');
        $this->actingAs($applicant)->get('/job_seeker/job-seeker-profile')->assertOk()->assertSee('My Dashboard')->assertSee('Passport information')->assertSee('Save personal information');
        $this->patch('/job_seeker/profile/basics',['first_name'=>'Example','last_name'=>'Applicant','nationality'=>'Fixture country'])->assertRedirect()->assertSessionHasNoErrors();
        $payload=['passport_number'=>'ZZ123456','holder_name'=>'Example Applicant','issuing_country'=>'Fixture country','issue_date'=>'2020-01-01','expiry_date'=>'2030-01-01'];
        $this->put('/job_seeker/profile/passport',$payload)->assertRedirect()->assertSessionHasNoErrors();
        $record=DB::table('applicant_passport_profiles')->where('user_id',$applicant->id)->first();
        $this->assertNotSame('ZZ123456',$record->number_encrypted);$this->assertSame('ZZ123456',decrypt($record->number_encrypted));
        $this->get('/job_seeker/job-seeker-profile')->assertOk()->assertSee('3456')->assertDontSee('ZZ123456');
        $other=$this->actor('job_seeker','another');$this->actingAs($other)->put('/job_seeker/profile/passport',$payload)->assertSessionHasErrors('passport_number');
        $this->assertNull(session()->getOldInput('passport_number'));
        $job=$this->job();DB::table('applications')->insert(['user_id'=>$applicant->id,'employer_id'=>$this->company->id,'job_id'=>$job->id,'status'=>1,'date_applied'=>now()->toDateString(),'expected_salary'=>10,'created_at'=>now(),'updated_at'=>now()]);
        $this->actingAs($applicant)->put('/job_seeker/profile/passport',array_merge($payload,['passport_number'=>'CHANGED123']))->assertSessionHasErrors('passport_number');
        $this->actingAs($this->employer)->put('/job_seeker/profile/passport',$payload)->assertForbidden();
    }
    public function test_hcaptcha_settings_keep_secret_encrypted_and_require_configuration_to_enable(): void
    {
        $this->admin->givePermissionTo(Permission::findOrCreate('application-setting','web'));
        $this->actingAs($this->admin)->get('/admin/security-settings')->assertOk()->assertSee('Human verification');
        $site='06243b52-2ab0-4044-9540-6a1cf3f44fd0';
        $this->put('/admin/security-settings',['enabled'=>1,'sitekey'=>$site])->assertSessionHasErrors('hcaptcha_secret');
        $this->put('/admin/security-settings',['enabled'=>1,'sitekey'=>$site,'hcaptcha_secret'=>'synthetic-secret'])->assertRedirect()->assertSessionHasNoErrors();
        $encrypted=DB::table('site_security_settings')->value('hcaptcha_secret_encrypted');$this->assertNotSame('synthetic-secret',$encrypted);$this->assertSame('synthetic-secret',decrypt($encrypted));
        $this->get('/admin/security-settings')->assertOk()->assertDontSee('synthetic-secret');
        $this->actingAs($this->employer)->get('/admin/security-settings')->assertForbidden();
    }

    public function test_public_and_applicant_filters_share_rules_period_normalization_and_pagination(): void
    {
        $job=$this->job();$job->update(['title'=>'Needle original','salary_type'=>' Monthly ','application_deadline'=>'2020-01-01']);
        for($i=1;$i<=11;$i++){$copy=$job->replicate();$copy->slug='needle-'.$i;$copy->title='Needle '.$i;$copy->save();}
        $query=['title'=>'nEeDlE','country_id'=>806,'category_id'=>806,'salary_type'=>'monthly'];
        $url='/search-jobs?'.http_build_query($query);
        $this->get($url)->assertOk()->assertViewHas('jobs',fn($j)=>$j->total()===12 && $j->count()===10)->assertSee('jg-page-nav',false)->assertDontSee('pagination-sm',false)->assertSee('Reset filters');
        $this->get($url.'&page=2')->assertOk()->assertViewHas('jobs',fn($j)=>$j->count()===2)->assertSee('country_id=806',false);
        $this->get($url.'&page=99')->assertRedirect();
        $this->get('/search-jobs?salary_type=weekly')->assertOk()->assertViewHas('jobs',fn($j)=>$j->total()===0);
        $applicant=$this->actor('job_seeker','global-applicant');$this->actingAs($applicant)->get('/job_seeker/find-jobs?'.http_build_query($query))->assertOk()->assertViewHas('jobs',fn($j)=>$j->total()===12)->assertSee('jg-profile-nav',false)->assertDontSee('application.js',false);
        $this->get('/job_seeker/jobseeker-change-password')->assertOk()->assertSee('Account security')->assertSee('jg-profile-nav',false)->assertDontSee('class="page-header"',false);
    }
    public function test_company_support_recipient_is_scoped_and_email_acceptance_is_recorded_without_losing_ticket(): void
    {
        config(['support_center.enabled'=>true,'mail.mailers.studio_smtp'=>['transport'=>'smtp','host'=>'smtp.example.test','username'=>'synthetic'],'email_studio.from_address'=>'sender@example.test','email_studio.from_name'=>'JoinGallagher']);
        \Illuminate\Support\Facades\Mail::fake();$this->job();$applicant=$this->actor('job_seeker','support-applicant');$center=app(\App\Services\SupportCenter::class);
        $ticket=$center->create($applicant,['category'=>'refund','subject'=>'Please review payment','description'=>'Private example request.','recipient_company_id'=>$this->company->id]);
        $this->assertTrue($center->canSee($this->employer,$ticket));
        $other=$this->actor('employer','unrelated-company');$this->assertFalse($center->canSee($other,$ticket));
        $reply=$center->reply($this->employer,$ticket->id,'We have received your request.',false);$this->assertNotNull($reply->id);
        $status=app(\App\Services\SupportNotifications::class)->notify($applicant,$ticket,'Private example request.','test:'.$ticket->id);$this->assertSame('accepted',$status);
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\SupportMessage::class,fn($m)=>$m->hasTo($this->employer->email));
        $this->assertDatabaseHas('support_mail_deliveries',['ticket_id'=>$ticket->id,'status'=>'accepted','attempts'=>1]);
        $delivery=DB::table('support_mail_deliveries')->where('ticket_id',$ticket->id)->first();$this->assertNotSame('Private example request.',$delivery->message_encrypted);
        $this->actingAs($applicant)->get('/support-center/tickets/'.$ticket->id)->assertOk();
        $this->actingAs($other)->get('/support-center/tickets/'.$ticket->id)->assertNotFound();
        config(['mail.mailers.studio_smtp'=>null]);
        $status=app(\App\Services\SupportNotifications::class)->notify($applicant,$ticket,'Still retained in the inbox.','failed:'.$ticket->id);$this->assertSame('failed',$status);$this->assertDatabaseHas('support_tickets',['id'=>$ticket->id]);
    }
}
