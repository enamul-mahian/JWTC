<?php

namespace Tests\Feature;

use App\Exceptions\Handler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SafeLogoutRecoveryTest extends TestCase
{
    public function test_get_logout_is_confirmation_only_and_preserves_csrf(): void
    {
        $this->get('/logout')->assertRedirect(route('logout.confirm'));
        $this->get(route('logout.confirm'))->assertOk()
            ->assertSee('Confirm sign out')
            ->assertSee('name="_token"', false);
        $this->assertTrue(in_array('POST', Route::getRoutes()->getByName('logout')->methods(), true));
    }

    public function test_expired_post_logout_returns_new_csrf_form_not_419(): void
    {
        $request = Request::create('/logout', 'POST');
        $response = app(Handler::class)->render($request, new TokenMismatchException());
        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('logout.confirm'), $response->headers->get('Location'));
    }

    public function test_other_csrf_mismatches_are_never_bypassed(): void
    {
        $request = Request::create('/admin/users', 'POST');
        $response = app(Handler::class)->render($request, new TokenMismatchException());
        $this->assertSame(419, $response->getStatusCode());
    }

    public function test_logout_confirmation_contains_only_post_with_csrf(): void
    {
        $path = base_path('resources/views/auth/logout-confirm.blade.php');
        $markup = file_get_contents($path);
        $this->assertStringContainsString('method="POST"', $markup);
        $this->assertStringContainsString('@csrf', $markup);
        $this->assertStringNotContainsString('method="GET"', $markup);
    }
}
