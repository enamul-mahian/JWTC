<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiRouteIntegrityTest extends TestCase
{
    public function test_all_api_routes_resolve_to_existing_public_controller_methods(): void
    {
        $checked = 0;
        foreach (Route::getRoutes() as $route) {
            if (!str_starts_with($route->uri(), 'api/')) {
                continue;
            }
            $action = $route->getActionName();
            if ($action === 'Closure') {
                continue;
            }
            [$class, $method] = str_contains($action, '@')
                ? explode('@', $action, 2) : [$action, '__invoke'];
            $this->assertTrue(class_exists($class), "Missing API controller: {$class}");
            $this->assertTrue(method_exists($class, $method), "Missing API action: {$action}");
            $reflection = new \ReflectionMethod($class, $method);
            $this->assertTrue($reflection->isPublic(), "API action is not public: {$action}");
            $checked++;
        }
        $this->assertGreaterThan(40, $checked, 'Expected the legacy API route surface to remain registered.');
    }
}
