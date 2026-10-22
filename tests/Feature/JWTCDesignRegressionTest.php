<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Frontend\HomeController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JWTCDesignRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', static function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('user_type')->index();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('countries', static function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(true);
        });
        Schema::create('job_categories', static function (Blueprint $table) { $table->id(); $table->string('name'); });
        Schema::create('companies', static function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->unsignedBigInteger('user_id')->nullable();
        });
        Schema::create('jobs', static function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('job_category_id')->nullable();
            $table->boolean('is_publish')->default(false);
            $table->date('application_deadline')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('applications', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('job_id');
            $table->timestamps();
        });
    }

    public function test_approved_design_is_enabled_by_default(): void
    {
        $this->assertTrue((bool) config('jwtc_design.enabled'));
    }

    public function test_approved_home_preserves_published_listings_and_hides_drafts(): void
    {
        config()->set('jwtc_design.enabled', true);

        DB::table('countries')->insert(['id' => 3, 'name' => 'United Kingdom']);
        DB::table('companies')->insert([
            'id' => 2, 'company_name' => 'Example Employer', 'user_id' => 5,
        ]);
        DB::table('jobs')->insert([
            [
                'id' => 10, 'title' => 'Live Customer Service',
                'company_id' => 2, 'country_id' => 3,
                'is_publish' => 1,
                'application_deadline' => now()->addDays(10)->toDateString(),
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 11, 'title' => 'Expired Position',
                'company_id' => 2, 'country_id' => 3,
                'is_publish' => 1,
                'application_deadline' => now()->subDay()->toDateString(),
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 12, 'title' => 'Unpublished Position',
                'company_id' => 2, 'country_id' => 3,
                'is_publish' => 0,
                'application_deadline' => now()->addDays(10)->toDateString(),
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        $view = app(HomeController::class)();
        $this->assertSame('frontend.index', $view->name());
        $latest = $view->getData()['latestJobs'];
        $this->assertCount(2, $latest);
        $this->assertFalse($latest->contains('title', 'Unpublished Position'));
        $this->assertSame('Live Customer Service', $latest->first()->title);
        $this->assertSame('Example Employer', $latest->first()->company->company_name);
    }

    public function test_confirmed_home_always_supplies_actual_job_data(): void
    {
        config()->set('jwtc_design.enabled', false);
        $view = app(HomeController::class)();
        $this->assertSame('frontend.index', $view->name());
        $this->assertArrayHasKey('latestJobs', $view->getData());
        $this->assertSame(0, $view->getData()['publishedJobCount']);
    }

    public function test_admin_overview_uses_real_counts_without_sample_revenue(): void
    {
        config()->set('jwtc_design.enabled', true);
        DB::table('users')->insert([
            ['id' => 1, 'user_type' => 'job_seeker', 'name'=>'First','email'=>'first@example.test'],
            ['id' => 2, 'user_type' => 'job_seeker', 'name'=>'Second','email'=>'second@example.test'],
            ['id' => 3, 'user_type' => 'employer', 'name'=>'Company','email'=>'company@example.test'],
        ]);
        DB::table('jobs')->insert([
            [
                'id' => 10, 'title' => 'Active Position',
                'is_publish' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 11, 'title' => 'Draft Position',
                'is_publish' => 0, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
        DB::table('applications')->insert([
            ['id' => 1, 'user_id' => 1, 'job_id' => 10],
            ['id' => 2, 'user_id' => 2, 'job_id' => 10],
        ]);

        $view = app(AdminDashboardController::class)->dashboard(Request::create('/admin/dashboard'));
        $this->assertSame('admin.dashboard.redesign', $view->name());
        $metrics = $view->getData()['metrics'];
        $this->assertSame(2, $metrics['job_seekers']);
        $this->assertSame(1, $metrics['employers']);
        $this->assertSame(1, $metrics['published_jobs']);
        $this->assertSame(2, $metrics['applications']);
        $this->assertCount(2, $view->getData()['recentJobs']);
    }

    public function test_existing_admin_dashboard_stays_default_while_flag_disabled(): void
    {
        config()->set('jwtc_design.enabled', false);
        $view = app(AdminDashboardController::class)->dashboard(Request::create('/admin/dashboard'));
        $this->assertSame('admin.dashboard.index', $view->name());
    }
    public function test_redesigned_public_homepage_renders_and_avoids_unused_map_sdk(): void
    {
        config()->set('jwtc_design.enabled', true);
        \Illuminate\Support\Facades\View::share([
            'setting' => (object) ['logo' => null, 'favicon' => null],
            'pages' => [],
        ]);

        DB::table('jobs')->insert([
            'id' => 14,
            'title' => 'Public Application Assistant',
            'is_publish' => 1,
            'application_deadline' => now()->addDays(3)->toDateString(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Find Your Next Opportunity')
            ->assertSee('Public Application Assistant')
            ->assertSee('jg-hero')
            ->assertSee('theme/approved.css')
            ->assertSee('Skip to main content')
            ->assertDontSee('AIzaSyBxJIqIIz', false);
    }

    public function test_redesigned_admin_dashboard_renders_without_vendor_demo_metrics(): void
    {
        config()->set('jwtc_design.enabled', true);
        (require database_path('migrations/2023_12_21_130613_create_permission_tables.php'))->up();

        DB::table('users')->insert([
            'id' => 19,
            'user_type' => 'admin',
            'name' => 'Design QA',
            'email' => 'qa@example.invalid',
        ]);

        $this->actingAs(\App\Models\User::findOrFail(19));
        // Direct view::render() bypasses the ShareErrorsFromSession middleware.
        \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag());
        \Illuminate\Support\Facades\View::share([
            'setting' => (object) ['logo' => null, 'favicon' => null],
            'pages' => [],
        ]);

        $view = app(AdminDashboardController::class)->dashboard(
            Request::create('/admin/dashboard')
        );
        $html = $view->render();
        $this->assertStringContainsString('Join Gallagher Dashboard', $html);
        $this->assertStringContainsString('Registered Applicants', $html);
        $this->assertStringContainsString('jw-admin-content', $html);
        $this->assertStringNotContainsString('Roman Joined the Team!', $html);
        $this->assertStringNotContainsString('$63,489.50', $html);
        $this->assertStringNotContainsString('Yearly Breakup', $html);
    }

}
