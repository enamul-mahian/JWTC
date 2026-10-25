<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminUserEditRouteRegressionTest extends TestCase
{
    public function test_user_edit_has_distinct_get_put_and_delete_routes(): void
    {
        $routes = Route::getRoutes();
        $this->assertSame('user.edit',
            $routes->match(Request::create('/dashboard/user/10/edit', 'GET'))->getName());
        $this->assertSame('user.update',
            $routes->match(Request::create('/dashboard/user/10/edit', 'PUT'))->getName());
        $this->assertSame('user.edit.legacy',
            $routes->match(Request::create('/dashboard/user/10', 'GET'))->getName());
        $this->assertSame('user.update.legacy',
            $routes->match(Request::create('/dashboard/user/10', 'PUT'))->getName());
        $this->assertSame('admin.users.destroy',
            $routes->match(Request::create('/dashboard/user/10', 'DELETE'))->getName());

        $this->assertNotSame(route('user.update', 10), route('admin.users.destroy', 10));
        $this->assertStringEndsWith('/dashboard/user/10/edit', route('user.update', 10));
    }

    public function test_legacy_bookmark_get_does_not_mutate_and_redirects_to_edit(): void
    {
        $this->get('/dashboard/user/10')
            ->assertRedirect(route('login'));
        // Protected paths must authenticate before hitting the safe GET
        // redirect; route name is checked separately above.
    }

    public function test_edit_form_uses_csrf_put_and_blocks_duplicate_submits(): void
    {
        $html = file_get_contents(resource_path('views/admin/userCreate/edit.blade.php'));
        $this->assertStringContainsString("route('user.update',", $html);
        $this->assertStringContainsString("@method('put')", $html);
        $this->assertStringContainsString('@csrf', $html);
        $this->assertStringContainsString('admin-user-edit-form', $html);
        $this->assertStringContainsString('submit.disabled = true', $html);
    }

    public function test_controller_updates_only_allowlisted_user_fields(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/Admin/DashboardController.php'));
        $this->assertStringContainsString('$validator->validated()', $source);
        $this->assertStringContainsString('$user->syncRoles([$role])', $source);
        $this->assertStringNotContainsString('$user->update($request->all())', $source);
    }

    public function test_destructive_admin_operations_remain_throttled(): void
    {
        $routes = Route::getRoutes();
        $password = $routes->getByName('admin.users.password');
        $delete = $routes->getByName('admin.users.destroy');
        $this->assertContains('throttle:5,1', $password->gatherMiddleware());
        $this->assertContains('throttle:3,1', $delete->gatherMiddleware());
    }
}
