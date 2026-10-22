<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckAdminuserMiddleware;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminRoleCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('user_type'); $table->timestamp('employer_approved_at')->nullable(); $table->tinyInteger('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        // Isolated middleware route avoids touching any real admin tables.
        Route::middleware(['web', CheckAdminuserMiddleware::class])
            ->get('/__test_jwtc_admin_guard', fn () => response('ADMIN_OK', 200));
    }

    public function test_canonical_and_legacy_admin_values_are_accepted(): void
    {
        foreach (['admin', 'Admin', 'super_admin'] as $i => $role) {
            $user = User::create([
                'name' => 'Synthetic admin', 'email' => 'admin'.$i.'@example.invalid',
                'user_type' => $role,
            ]);
            $user->forceFill(['employer_approved_at' => now()])->save();
            $this->actingAs($user)->get('/__test_jwtc_admin_guard')
                ->assertOk()->assertSee('ADMIN_OK');
        }
    }

    public function test_employers_staff_and_jobseekers_never_gain_admin_route_access(): void
    {
        foreach (['employer', 'job_seeker', 'staff'] as $i => $role) {
            $user = User::create([
                'name' => 'Synthetic non-admin', 'email' => 'nonadmin'.$i.'@example.invalid',
                'user_type' => $role,
            ]);
            $user->forceFill(['employer_approved_at' => now()])->save();
            $this->actingAs($user)->get('/__test_jwtc_admin_guard')
                ->assertForbidden();
        }
    }
}
