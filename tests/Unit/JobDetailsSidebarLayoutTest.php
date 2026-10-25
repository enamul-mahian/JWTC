<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class JobDetailsSidebarLayoutTest extends TestCase
{
    private function source(string $path): string
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/'.$path);
        $this->assertNotFalse($source, 'Missing file: '.$path);
        return $source;
    }

    public function test_job_details_loads_last_scoped_styles_and_only_one_sidebar(): void
    {
        $layout = $this->source('resources/views/frontend/layout/frontMaster.blade.php');
        $view = $this->source('resources/views/frontend/pages/job/details.blade.php');

        $this->assertTrue(strpos($layout, "@stack('page-style')") >
            strpos($layout, "asset('theme/workspace.css')"));
        $this->assertStringContainsString("asset('theme/job-details-layout.css')", $view);
        $this->assertStringContainsString('jg-job-details', $view);
        $this->assertStringContainsString('jg-job-detail-grid', $view);
        $this->assertStringContainsString('jg-detail-sidebar', $view);
        $this->assertStringContainsString('jg-detail-main', $view);
        $this->assertSame(1, substr_count($view, "@include('frontend.pages.employer.sideMenu')"));
        $this->assertStringNotContainsString('<div class="right-sideabr">', $view);
    }

    public function test_job_details_preserves_job_info_and_external_apply_links(): void
    {
        $view = $this->source('resources/views/frontend/pages/job/details.blade.php');

        $this->assertStringContainsString('$jobDetails?->title', $view);
        $this->assertStringContainsString('$jobDetails?->job_responsibilities', $view);
        $this->assertStringContainsString('JobPresentation::salarySummary', $view);
        $this->assertStringContainsString('$jobDetails?->external_apply_url', $view);
        $this->assertStringContainsString('rel="noopener noreferrer"', $view);
    }

    public function test_css_overrides_white_sidebar_heading_and_respects_mobile(): void
    {
        $css = $this->source('public/theme/job-details-layout.css');

        $this->assertStringContainsString('.jg-detail-sidebar .right-sideabr h4', $css);
        $this->assertStringContainsString('background:transparent!important', $css);
        $this->assertStringContainsString('color:#fff!important', $css);
        $this->assertStringContainsString('grid-template-columns:230px minmax(0,1fr)', $css);
        $this->assertStringContainsString('grid-template-columns:minmax(0,1fr)!important', $css);
        $this->assertStringContainsString('@media(max-width:600px)', $css);
    }
}
