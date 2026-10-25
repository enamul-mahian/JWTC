<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EmployerWorkspaceLayoutTest extends TestCase
{
    private function source(string $path): string
    {
        $contents = file_get_contents(dirname(__DIR__, 2).'/'.$path);
        $this->assertNotFalse($contents, 'Missing file: '.$path);

        return $contents;
    }

    public function test_job_lists_and_manage_jobs_load_isolated_styles_after_global_css(): void
    {
        $layout = $this->source('resources/views/frontend/layout/frontMaster.blade.php');
        $this->assertTrue(
            strpos($layout, "@stack('page-style')") > strpos($layout, "asset('theme/workspace.css')"),
            'Page styles must load after the global workspace stylesheet.'
        );

        foreach ([
            'resources/views/frontend/pages/job/list.blade.php',
            'resources/views/frontend/pages/manegeJob/list.blade.php',
        ] as $path) {
            $view = $this->source($path);
            $this->assertStringContainsString("asset('theme/employer-jobs.css')", $view);
            $this->assertStringContainsString('jg-employer-jobs', $view);
            $this->assertStringContainsString("@include('frontend.pages.employer.sideMenu')", $view);
            $this->assertStringNotContainsString(
                '<div class="right-sideabr">'.PHP_EOL.'                            @include',
                $view,
                'The side-menu partial already supplies its own sidebar container.'
            );
        }
    }

    public function test_employer_intro_and_job_cards_keep_accessible_actions(): void
    {
        $view = $this->source('resources/views/frontend/pages/job/list.blade.php');

        $this->assertStringContainsString('jw-workspace-intro jg-employer-intro', $view);
        $this->assertStringContainsString('jg-employer-job-card', $view);
        $this->assertStringContainsString('jg-employer-card-actions', $view);
        $this->assertStringContainsString('aria-label="View applications for', $view);
        $this->assertStringContainsString('aria-label="Delete', $view);
        $this->assertStringContainsString('aria-label="Restore', $view);
        $this->assertStringNotContainsString('<a href="{{ route(\'jobs.edit\', $job) }}" class=" mr-3">', $view);
        $this->assertStringContainsString("route('jobs.destroy', \$job)", $view);
        $this->assertStringContainsString("route('archive-jobs.restore', \$job)", $view);
    }

    public function test_css_is_scoped_and_responsive_for_employer_pages(): void
    {
        $css = $this->source('public/theme/employer-jobs.css');

        $this->assertStringContainsString('.jg-site .jg-employer-intro', $css);
        $this->assertStringContainsString('#latest-jobs.jg-employer-jobs.section', $css);
        $this->assertStringContainsString('grid-template-columns:230px minmax(0,1fr)', $css);
        $this->assertStringContainsString('@media(max-width:850px)', $css);
        $this->assertStringContainsString('@media(max-width:600px)', $css);
        $this->assertStringContainsString(':focus-visible', $css);
    }
}
