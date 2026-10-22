<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApplicationCodeRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Isolated fixtures; avoids touching the production schema or running
        // unrelated legacy migrations during authentication regression tests.
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_type')->nullable();
            $table->string('password')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('unique_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('code')->nullable()->unique();
            $table->date('date');
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });

        $userId = DB::table('users')->insertGetId([
            'name' => 'Applicant Test',
            'email' => 'applicant@example.invalid',
            'user_type' => 'job_seeker',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::findOrFail($userId));
    }

    public function test_code_request_is_not_a_get_operation(): void
    {
        $this->assertContains('POST', Route::getRoutes()->getByName('request-code')->methods());
        $this->assertNotContains('GET', Route::getRoutes()->getByName('request-code')->methods());
    }

    public function test_only_one_outstanding_request_and_new_request_after_consumption(): void
    {
        $url = route('request-code');

        $this->post($url)->assertRedirect(route('unique_codes'));
        $this->assertDatabaseCount('unique_codes', 1);
        $this->post($url)->assertRedirect(route('unique_codes'));
        $this->assertDatabaseCount('unique_codes', 1);

        DB::table('unique_codes')->update(['code' => 'TEST-APPROVED-1']);
        $this->post($url)->assertRedirect(route('unique_codes'));
        $this->assertDatabaseCount('unique_codes', 1);

        DB::table('unique_codes')->update(['is_used' => 1]);
        $this->post($url)->assertRedirect(route('unique_codes'));
        $this->assertDatabaseCount('unique_codes', 2);
    }
}
