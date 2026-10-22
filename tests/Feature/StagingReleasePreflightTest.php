<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StagingReleasePreflightTest extends TestCase
{
    public function test_production_environment_is_never_inspected_or_mutated(): void
    {
        config()->set('app.env', 'production');
        DB::shouldReceive('connection')->never();

        $this->artisan('jwtc:staging-preflight', [
            '--expected-db' => 'sample_staging',
            '--production-db' => 'sample_live',
            '--expected-url' => 'https://staging.example.invalid',
            '--production-root' => base_path(),
            '--uploads' => ['storage/app','public/images'],
        ])->expectsOutputToContain('APP_ENV must be exactly staging.')
            ->assertExitCode(1);
    }

    public function test_staging_requires_explicit_distinct_database_identifiers(): void
    {
        config()->set('app.env', 'staging');
        config()->set('app.url', 'https://staging.example.invalid');
        config()->set('app.debug', false);
        DB::shouldReceive('connection')->never();

        $this->artisan('jwtc:staging-preflight', [
            '--expected-db' => 'same_name',
            '--production-db' => 'same_name',
            '--expected-url' => 'https://staging.example.invalid',
            '--production-root' => base_path(),
            '--uploads' => ['storage/app','public/images'],
        ])->expectsOutputToContain('Supply different, nonempty')
            ->assertExitCode(1);
    }

    public function test_existing_live_root_must_not_equal_current_root(): void
    {
        config()->set('app.env', 'staging');
        config()->set('app.url', 'https://staging.example.invalid');
        config()->set('app.debug', false);
        DB::shouldReceive('connection')->never();

        $this->artisan('jwtc:staging-preflight', [
            '--expected-db' => 'sample_staging',
            '--production-db' => 'sample_live',
            '--expected-url' => 'https://staging.example.invalid',
            '--production-root' => base_path(),
            '--uploads' => ['storage/app','public/images'],
        ])->expectsOutputToContain('distinct from staging')
            ->assertExitCode(1);
    }

    public function test_path_traversal_and_outside_root_are_blocked_before_database_connection(): void
    {
        config()->set('app.env', 'staging');
        config()->set('app.url', 'https://staging.example.invalid');
        config()->set('app.debug', false);
        DB::shouldReceive('connection')->never();

        $this->artisan('jwtc:staging-preflight', [
            '--expected-db' => 'sample_staging',
            '--production-db' => 'sample_live',
            '--expected-url' => 'https://staging.example.invalid',
            '--production-root' => dirname(base_path()),
            '--uploads' => ['storage/app', '../secret'],
        ])->expectsOutputToContain('unsafe relative path')
            ->assertExitCode(1);
    }

    public function test_sqlite_test_environment_is_always_rejected(): void
    {
        config()->set('app.env', 'staging');
        config()->set('app.url', 'https://staging.example.invalid');
        config()->set('app.debug', false);
        DB::shouldReceive('connection')->never();

        $this->artisan('jwtc:staging-preflight', [
            '--expected-db' => 'sample_staging',
            '--production-db' => 'sample_live',
            '--expected-url' => 'https://staging.example.invalid',
            '--production-root' => dirname(base_path()),
            '--uploads' => ['storage/app','public/images'],
        ])->assertExitCode(1);
    }
}
