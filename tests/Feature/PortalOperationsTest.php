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
        Role::findOrCreate('Admin','web');$this->admin->assignRole('Admin');$center->reply($this->admin,$ticket->id,'Internal review note.',true);
        $this->assertFalse($center->replies($this->employer,$ticket)->getCollection()->contains('body','Internal review note.'));
        config(['email_studio.enabled'=>true]);$this->admin->forceFill(['user_type'=>'Admin'])->save();$this->assertSame('admin',$this->admin->user_type);$this->assertDatabaseHas('users',['id'=>$this->admin->id,'user_type'=>'Admin']);$this->actingAs($this->admin)->get('/admin/email-studio')->assertOk();
        $other=$this->actor('employer','unrelated-company');$other->forceFill(['employer_approved_at'=>now()])->save();$this->assertFalse($center->canSee($other,$ticket));
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
    public function test_admin_creates_custom_job_with_company_owner_and_audit(): void
    {
        $this->actingAs($this->admin)->get('/admin/jobs/create')
            ->assertOk()->assertSee('Create custom job')->assertSee('Fixture company');
        $payload=$this->payload();
        $payload['is_publish']=1;
        $payload['posting_permission']=1;
        $payload['user_id']=$this->admin->id; // Never trust request-supplied ownership.
        $this->post('/admin/jobs',$payload)->assertRedirect()->assertSessionHasNoErrors();
        $created=Job::where('title','New fixture vacancy')->firstOrFail();
        $this->assertSame($this->company->id,(int)$created->company_id);
        $this->assertSame($this->employer->id,(int)$created->user_id);
        $this->assertSame(1,(int)$created->is_publish);
        $this->assertDatabaseHas('job_edit_events',['job_id'=>$created->id,'actor_id'=>$this->admin->id]);
        $this->actingAs($this->employer)->get('/admin/jobs/create')->assertForbidden();
    }

    public function test_unapproved_employer_cannot_be_selected_for_admin_custom_post(): void
    {
        $this->employer->forceFill(['employer_approved_at'=>null])->save();
        $payload=$this->payload();
        $payload['posting_permission']=1;
        $payload['is_publish']=0;
        $this->actingAs($this->admin)->post('/admin/jobs',$payload)
            ->assertSessionHasErrors('company_id');
        $this->assertDatabaseMissing('jobs',['title'=>'New fixture vacancy']);
    }

    public function test_external_vacancy_requires_approval_and_explicit_publication_without_changing_jobs(): void
    {
        $id=DB::table('external_job_imports')->insertGetId([
            'source'=>'uk_government_jobs',
            'source_url'=>'https://www.jobs.service.gov.uk/jobs/6ad61edeb0af0cb429b49a30',
            'source_hash'=>hash('sha256','https://www.jobs.service.gov.uk/jobs/6ad61edeb0af0cb429b49a30'),
            'title'=>'External fixture vacancy', 'company_name'=>'Original employer',
            'location'=>'London', 'salary'=>'£30,000 a year',
            'posted_on'=>today()->subDays(1)->toDateString(),
            'application_deadline'=>today()->addDays(8)->toDateString(),
            'workplace'=>'onsite','employment_type'=>'permanent','schedule'=>'full_time',
            'status'=>'approved','imported_by'=>$this->admin->id,
            'created_at'=>now(),'updated_at'=>now(),
        ]);
        $this->get('/government-jobs/'.$id)->assertNotFound();
        $jobs=DB::table('jobs')->count();
        $this->actingAs($this->admin)->post('/admin/external-jobs/'.$id.'/publication',
            ['action'=>'publish'])->assertSessionHasErrors();
        $this->post('/admin/external-jobs/'.$id.'/publication',
            ['action'=>'publish','publishing_rights'=>1])->assertRedirect();
        $this->assertDatabaseHas('external_job_imports',['id'=>$id,'status'=>'published']);
        $this->get('/government-jobs/'.$id)->assertOk()
            ->assertSee('External fixture vacancy')->assertSee('Original employer')
            ->assertSee('View original vacancy and apply');
        $this->get('/search-jobs')->assertOk()->assertSee('External fixture vacancy');
        $this->assertSame($jobs,DB::table('jobs')->count());
        $this->post('/admin/external-jobs/'.$id.'/publication',['action'=>'publish','publishing_rights'=>1])
            ->assertSessionHasErrors();
        $this->actingAs($this->employer)->post('/admin/external-jobs/'.$id.'/publication',
            ['action'=>'unpublish'])->assertForbidden();
        $this->actingAs($this->admin)->post('/admin/external-jobs/'.$id.'/publication',
            ['action'=>'unpublish'])->assertRedirect();
        $this->get('/government-jobs/'.$id)->assertNotFound();
        $this->assertDatabaseHas('external_job_imports',['id'=>$id,'status'=>'approved']);
    }


    public function test_admin_can_import_pasted_work_hub_text_without_remote_fetch_or_live_job_changes(): void
    {
        $url = 'https://www.jobs.service.gov.uk/jobs/6ad095af813f93ba4140e36e';
        $pasted = "Multi Skilled Kitchen Fitter\n"
            ."Company: Reading Borough Council\n"
            ."Salary: £32,293 to £38,262 a year\n"
            ."Hours: Full-time\n"
            ."Location: Reading, RG2 0QX\n"
            ."Working pattern: On-site\n"
            ."Job type: Permanent\n"
            ."Posting date: ".today()->subDays(2)->format('j M Y')."\n"
            ."Closing date: ".today()->addDays(4)->format('j M Y')."\n"
            ."Summary\nReliable carpentry and kitchen installation.\n";
        $before = DB::table('jobs')->count();
        $this->actingAs($this->admin)->get('/admin/external-jobs')
            ->assertOk()->assertSee('Paste the vacancy text');
        $this->post('/admin/external-jobs/from-text', [
            'source_url' => $url, 'vacancy_text' => $pasted,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('external_job_imports', [
            'source_url' => $url, 'company_name' => 'Reading Borough Council',
            'title' => 'Multi Skilled Kitchen Fitter', 'status' => 'draft',
        ]);
        $this->assertSame($before, DB::table('jobs')->count());
        $this->post('/admin/external-jobs/from-text', [
            'source_url' => $url, 'vacancy_text' => $pasted,
        ])->assertRedirect();
        $this->assertSame(1, DB::table('external_job_imports')->where('source_url', $url)->count());
        $this->actingAs($this->employer)->post('/admin/external-jobs/from-text', [
            'source_url' => $url, 'vacancy_text' => $pasted,
        ])->assertForbidden();
    }


    public function test_admin_manages_degree_options_without_deleting_historic_job_references(): void
    {
        $this->actingAs($this->admin)->get('/admin/job-degrees')
            ->assertOk()->assertSee('Manage Education &amp; Degrees', false)
            ->assertSee('Add degree');

        $this->post('/admin/job-degrees/levels', ['name' => 'Undergraduate'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $levelId = (int) DB::table('degree_lavels')->where('name', 'Undergraduate')->value('id');
        $this->assertGreaterThan(0, $levelId);

        $this->post('/admin/job-degrees/degrees', [
            'name' => 'Bachelor of Arts', 'degree_lavel_id' => $levelId,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $degreeId = (int) DB::table('degree_names')->where('name', 'Bachelor of Arts')->value('id');
        $this->assertGreaterThan(0, $degreeId);

        $this->post('/admin/job-degrees/degrees', [
            'name' => ' bachelor of arts ', 'degree_lavel_id' => $levelId,
        ])->assertSessionHasErrors('name');
        $this->assertSame(1, DB::table('degree_names')->where('degree_lavel_id', $levelId)->count());

        $job = $this->job();
        $job->update(['degree_level_id' => $levelId, 'degree_name_id' => $degreeId]);
        $jobId = $job->id;

        $this->patch('/admin/job-degrees/degrees/'.$degreeId, [
            'name' => 'Bachelor of Fine Arts', 'degree_lavel_id' => $levelId,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->patch('/admin/job-degrees/degrees/'.$degreeId.'/status', [
            'enabled' => 0,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('degree_names', [
            'id' => $degreeId, 'name' => 'Bachelor of Fine Arts', 'status' => 0,
        ]);
        $this->assertDatabaseHas('jobs', ['id' => $jobId, 'degree_name_id' => $degreeId]);

        $this->get('/admin/jobs/'.$jobId.'/edit')
            ->assertOk()->assertSee('Bachelor of Fine Arts')
            ->assertSee('Add, rename or disable Degree options');
        $this->actingAs($this->employer)->get('/jobs/create')
            ->assertOk()->assertDontSee('Bachelor of Fine Arts');
        $this->post('/admin/job-degrees/degrees', [
            'name' => 'Employer injected', 'degree_lavel_id' => $levelId,
        ])->assertForbidden();
        $this->assertDatabaseMissing('degree_names', ['name' => 'Employer injected']);
    }

    public function test_education_level_disable_is_reversible_and_preserves_job_selected_degree(): void
    {
        $levelId = DB::table('degree_lavels')->insertGetId([
            'name' => 'Academic Level', 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $degreeId = DB::table('degree_names')->insertGetId([
            'name' => 'Associate Degree', 'degree_lavel_id' => $levelId, 'status' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $job = $this->job();
        $job->update(['degree_level_id' => $levelId, 'degree_name_id' => $degreeId]);
        $this->actingAs($this->admin)
            ->patch('/admin/job-degrees/levels/'.$levelId.'/status', ['enabled' => 0])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('degree_lavels', ['id' => $levelId, 'status' => 0]);
        $this->get('/admin/jobs/'.$job->id.'/edit')->assertOk()->assertSee('Associate Degree');
        $this->actingAs($this->employer)->get('/jobs/create')->assertOk()->assertDontSee('Associate Degree');
        $this->actingAs($this->admin)
            ->patch('/admin/job-degrees/levels/'.$levelId.'/status', ['enabled' => 1])
            ->assertRedirect();
        $this->actingAs($this->employer)->get('/jobs/create')->assertOk()->assertSee('Associate Degree');
        $this->assertDatabaseHas('jobs', ['id' => $job->id, 'degree_name_id' => $degreeId]);
    }


    public function test_hourly_job_faq_calculates_illustrative_monthly_amount_without_changing_original_salary(): void
    {
        $job=$this->job();
        DB::table('countries')->where('id', 806)->update(['name' => 'United Kingdom']);
        $job->update([
            'salary_type'=>'hourly','minimum_salary'=>13,'maximum_salary'=>16,
            'faqs'=>json_encode([['question'=>'Is accommodation included?','answer'=>'Ask the employer about housing.']]),
        ]);
        $faqs=\App\Services\JobPresentation::faqs($job->fresh());
        $this->assertStringContainsString('37.5', $faqs[1]['answer']);
        $this->assertStringContainsString('2,112.50', $faqs[1]['answer']);
        $this->assertStringContainsString('not a confirmed monthly salary', $faqs[1]['answer']);
        $this->assertStringContainsString('£12.71', $faqs[0]['answer']);
        $this->assertTrue(collect($faqs)->contains(
            fn ($f) => $f['question']==='Is accommodation included?'
        ));
        $this->assertSame(13, $job->fresh()->minimum_salary);
        $this->assertSame('hourly', $job->fresh()->salary_type);
    }

    public function test_implausible_legacy_monthly_and_weekly_pay_does_not_appear_as_confirmed_salary(): void
    {
        $job=$this->job();
        $job->update(['minimum_salary'=>11,'maximum_salary'=>16,'salary_type'=>'monthly']);
        $this->assertStringContainsString('requires employer confirmation',
            \App\Services\JobPresentation::salarySummary($job->fresh()));
        $faqs=\App\Services\JobPresentation::faqs($job->fresh());
        $this->assertStringContainsString('Not calculated', $faqs[1]['answer']);
        $this->get('/vacancies/'.$job->id)->assertOk()
            ->assertSee('Pay period requires employer confirmation');
    }

    public function test_ireland_job_faq_uses_euro_statutory_reference_without_fabricating_converted_pay(): void
    {
        $job=$this->job();
        $countryId=DB::table('countries')->insertGetId(['name'=>'Ireland','code'=>'IE']);
        $job->update(['country_id'=>$countryId,'salary_type'=>'hourly','minimum_salary'=>15,'maximum_salary'=>18]);
        $faq=\App\Services\JobPresentation::faqs($job->fresh()->load('country'));
        $this->assertStringContainsString('€14.15', $faq[0]['answer']);
        $this->assertStringContainsString('EUR versus GBP', $faq[0]['answer']);
        $this->assertDatabaseHas('jobs',['id'=>$job->id,'minimum_salary'=>15]);
    }

    public function test_2026_uk_salary_benchmark_is_visible_but_not_represented_as_employer_offer(): void
    {
        $job=$this->job();
        DB::table('countries')->where('id',806)->update(['name'=>'United Kingdom']);
        $job->update([
            'minimum_salary'=>13, 'maximum_salary'=>16, 'salary_type'=>'hourly',
            'additional_salary_info'=>'<p>2026 indicative salary benchmark (not employer-confirmed pay)</p>',
            'faqs'=>json_encode([
                ['question'=>'What is the salary?','answer'=>'Legacy £8 per week'],
                ['question'=>'Is accommodation provided?','answer'=>'Ask the employer'],
            ]),
        ]);
        $current=$job->fresh()->load('country');
        $this->assertStringContainsString('indicative 2026 market benchmark',\App\Services\JobPresentation::salarySummary($current));
        $this->assertStringContainsString('NOT employer-confirmed',\App\Services\JobPresentation::salarySummary($current));
        $faqs=\App\Services\JobPresentation::faqs($current);
        $this->assertStringContainsString('£12.71', $faqs[0]['answer']);
        $this->assertStringContainsString('£2,112.50', $faqs[1]['answer']);
        $this->assertTrue(collect($faqs)->contains(fn($f)=>$f['question']==='Is accommodation provided?'));
        $this->assertFalse(collect($faqs)->contains(fn($f)=>str_contains($f['answer'],'Legacy £8')));
    }

    public function test_ireland_salary_benchmark_uses_euro_without_changing_job_jurisdiction(): void
    {
        $job=$this->job();
        $id=DB::table('countries')->insertGetId(['name'=>'Ireland','code'=>'IE']);
        $job->update([
            'country_id'=>$id,'minimum_salary'=>16,'maximum_salary'=>19,'salary_type'=>'hourly',
            'additional_salary_info'=>'2026 indicative salary benchmark (not employer-confirmed pay)',
        ]);
        $current=$job->fresh()->load('country');
        $display=\App\Services\JobPresentation::salarySummary($current);
        $this->assertStringContainsString('€16.00', $display);
        $this->assertStringNotContainsString('£16.00', $display);
        $this->assertStringContainsString('€14.15',\App\Services\JobPresentation::faqs($current)[0]['answer']);
        $this->assertSame($id,(int)$job->fresh()->country_id);
    }

    public function test_annual_benchmark_uses_annual_to_monthly_division_in_faq(): void
    {
        $job=$this->job();
        $job->update([
            'minimum_salary'=>30000,'maximum_salary'=>42000,'salary_type'=>'yearly',
            'additional_salary_info'=>'2026 indicative salary benchmark (not employer-confirmed pay)',
        ]);
        $current=$job->fresh();
        $this->assertStringContainsString('30,000.00',\App\Services\JobPresentation::salarySummary($current));
        $this->assertStringContainsString('2,500.00',\App\Services\JobPresentation::faqs($current)[1]['answer']);
        $this->assertStringContainsString('3,500.00',\App\Services\JobPresentation::faqs($current)[1]['answer']);
    }


    public function test_public_job_search_prioritizes_open_vacancies_and_preserves_closed_history(): void
    {
        $live=$this->job();
        $live->update([
            'title'=>'Open Fixture Vacancy',
            'application_deadline'=>today()->addDays(5)->toDateString(),
            'created_at'=>today()->subMonth(),
        ]);
        $closed=Job::create([
            'user_id'=>$this->employer->id,'company_id'=>$this->company->id,
            'job_category_id'=>806,'country_id'=>806,
            'title'=>'Closed Fixture Vacancy',
            'slug'=>'closed-fixture-post-'.uniqid(),
            'job_status'=>'Approved','salary_type'=>'hourly',
            'resume_receiving_email'=>'jobs@example.test',
            'minimum_salary'=>14,'maximum_salary'=>17,
            'job_responsibilities'=>'Historic role information',
            'application_deadline'=>today()->subDay()->toDateString(),
            'is_publish'=>1,'created_at'=>today(),'updated_at'=>today(),
        ]);
        $originalIds=Job::orderBy('id')->pluck('id')->all();
        $response=$this->get('/search-jobs');
        $response->assertOk()
            ->assertSee('1 applications open')
            ->assertSee('1 with passed deadlines')
            ->assertSeeInOrder(['Open Fixture Vacancy','Closed Fixture Vacancy'])
            ->assertSee('Applications closed')
            ->assertSee('Applications open');
        $this->get('/search-jobs?status=open')->assertOk()
            ->assertSee('Open Fixture Vacancy')->assertDontSee('Closed Fixture Vacancy');
        $this->get('/search-jobs?status=closed')->assertOk()
            ->assertSee('Closed Fixture Vacancy')->assertDontSee('Open Fixture Vacancy');
        $this->get('/vacancies/'.$closed->id)->assertOk()
            ->assertSee('The application deadline for this listing has passed.');
        $this->get('/')->assertOk()->assertSee('1</strong>Open applications',false);
        $this->assertSame($originalIds,Job::orderBy('id')->pluck('id')->all());
    }

    public function test_job_search_application_status_validation_does_not_accept_unknown_values(): void
    {
        $this->getJson('/search-jobs?status=deleted')->assertUnprocessable();
    }


    public function test_public_contact_form_saves_only_validated_fields_and_rejects_overlong_messages(): void
    {
        $this->get('/contact')->assertOk()
            ->assertSee('maxlength="255"', false)
            ->assertSee('type="email"', false);

        $payload = [
            'name' => 'Guest Reporter',
            'email' => 'guest-reporter@example.test',
            'subject' => 'Question about a listing',
            'message' => str_repeat('A', 256),
            'created_at' => '2000-01-01 00:00:00',
            'id' => 12345,
        ];

        $this->post('/send-contact-data', $payload)->assertRedirect()
            ->assertSessionHasErrors('message');
        $this->assertDatabaseMissing('contacts', [
            'email' => 'guest-reporter@example.test',
        ]);

        $payload['message'] = 'Please verify that the vacancy is open.';
        $this->post('/send-contact-data', $payload)->assertRedirect('/contact')
            ->assertSessionHasNoErrors();
        $saved = DB::table('contacts')->where('email', 'guest-reporter@example.test')->first();
        $this->assertNotNull($saved);
        $this->assertSame('Please verify that the vacancy is open.', $saved->message);
        $this->assertNotSame(12345, (int)$saved->id);
        $this->assertNotSame('2000-01-01 00:00:00', $saved->created_at);
    }


    public function test_administrator_deadline_plan_covers_all_27_jobs_without_backdating_original_post(): void
    {
        $ids=[11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,28,29,30,31,32,33,34,35,36,37,38];
        $found=[];
        foreach($ids as $id) {
            $deadline=\App\Services\JobDateRevision::deadlineForId($id);
            $reference=\App\Services\JobDateRevision::referenceForId($id);
            $this->assertNotNull($deadline);
            $this->assertContains($deadline,[
                '2026-11-27','2026-11-28','2026-11-29',
                '2026-11-30','2026-12-01','2026-12-02',
            ]);
            $this->assertContains($reference,['2026-10-14','2026-10-15','2026-10-16']);
            $found[]=$deadline;
        }
        $this->assertCount(27,$found);
        $this->assertNull(\App\Services\JobDateRevision::deadlineForId(9999));
        $this->assertNull(\App\Services\JobDateRevision::referenceForId(9999));
    }

    public function test_administrator_revised_deadline_is_clearly_labeled_and_original_job_date_preserved(): void
    {
        $job=$this->job();
        $originalCreated=$job->created_at->format('Y-m-d H:i:s');
        DB::table('jobs')->where('id',$job->id)->update([
            'id'=>11,'application_deadline'=>'2026-11-27',
        ]);
        $record=Job::findOrFail(11);
        $this->assertTrue(\App\Services\JobDateRevision::applies($record));
        $this->assertStringContainsString('not been independently confirmed',
            \App\Services\JobDateRevision::note($record));
        $this->get('/vacancies/11')->assertOk()
            ->assertDontSee("Website administrator's date revision")
            ->assertDontSee('Administrative listing reference date')
            ->assertSee('Application deadline: 27 Nov 2026')
            ->assertSee('website-updated; confirm with employer');
        $this->get('/search-jobs')->assertOk()
            ->assertSee('Closing date (verify with employer)')
            ->assertDontSee('Closing date revised by site administrator')
            ->assertDontSee('Site reference:')
            ->assertSee('Originally posted');
        $this->assertSame($originalCreated,$record->fresh()->created_at->format('Y-m-d H:i:s'));
        $record->update(['application_deadline'=>'2026-12-20']);
        $this->assertFalse(\App\Services\JobDateRevision::applies($record->fresh()));
    }


}
