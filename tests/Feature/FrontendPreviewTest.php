<?php

namespace Tests\Feature;

use Tests\TestCase;

class FrontendPreviewTest extends TestCase
{
    public function test_preview_routes_are_local_only_and_all_preview_pages_render(): void
    {
        $this->get('/preview/login')->assertNotFound();

        $this->app->detectEnvironment(fn () => 'local');
        $this->app['router']->middleware('web')->group(base_path('routes/frontend-preview.php'));
        $this->app['router']->getRoutes()->refreshNameLookups();
        $this->app['router']->getRoutes()->refreshActionLookups();

        foreach ([
            '/preview/login',
            '/preview/register',
            '/preview/student/dashboard',
            '/preview/student/issues',
            '/preview/student/issues/create',
            '/preview/student/issues/sample',
            '/preview/admin/dashboard',
            '/preview/admin/issues',
            '/preview/admin/issues/sample',
            '/preview/admin/issues/sample/edit',
        ] as $uri) {
            $this->get($uri)->assertOk();
        }
    }
}
