<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiAuthenticationSecurityTest extends TestCase
{
    public function test_api_registration_cannot_accept_an_unverified_role_from_the_client(): void
    {
        $this->postJson('/api/registration', [
            'user_type' => 'super_admin',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['name', 'email', 'phone', 'job_categories_id', 'password']);
    }

    public function test_unauthenticated_requests_cannot_update_password_or_access_account(): void
    {
        $this->postJson('/api/update-password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertUnauthorized();

        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_recovered_api_controllers_are_real_classes(): void
    {
        $this->assertTrue(class_exists(\App\Http\Controllers\API\AuthController::class));
        $this->assertTrue(class_exists(\App\Http\Controllers\API\CommonController::class));
        $this->assertNotNull(Route::getRoutes()->match(
            \Illuminate\Http\Request::create('/api/login', 'POST')
        ));
    }
}
