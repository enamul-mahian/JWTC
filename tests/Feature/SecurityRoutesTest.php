<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityRoutesTest extends TestCase
{
    public function test_sensitive_mutations_cannot_be_invoked_by_get(): void
    {
        $routes = [
            'request-code' => 'POST',
            'uploaded-cv-delete' => 'DELETE',
            'delete-jobseeer-certificate' => 'DELETE',
            'delete-upload-document' => 'DELETE',
            'delete.account' => 'DELETE',
            'database-backup' => 'POST',
        ];

        foreach ($routes as $name => $expected) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route, "Missing route: {$name}");
            $this->assertContains($expected, $route->methods());
            $this->assertNotContains('GET', $route->methods());
        }
    }
}
