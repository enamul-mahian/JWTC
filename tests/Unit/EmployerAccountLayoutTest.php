<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class EmployerAccountLayoutTest extends TestCase
{
    private function source(string $path): string
    {
        $content = file_get_contents(dirname(__DIR__, 2).'/'.$path);
        $this->assertNotFalse($content, 'Missing '. $path);
        return $content;
    }

    public function test_company_and_profile_use_the_same_responsive_sidebar_grid(): void
    {
        foreach ([
            'resources/views/frontend/pages/employer/update_profile.blade.php',
            'resources/views/frontend/pages/employer/companyInformation.blade.php',
        ] as $file) {
            $html = $this->source($file);
            $this->assertStringContainsString('jg-employer-account', $html);
            $this->assertStringContainsString('jg-employer-account-grid', $html);
            $this->assertStringContainsString('jg-account-side', $html);
            $this->assertStringContainsString('jg-account-main', $html);
            $this->assertStringContainsString("asset('theme/employer-account.css')", $html);
            $this->assertStringContainsString("@include('frontend.pages.employer.sideMenu')", $html);
            $this->assertStringNotContainsString(
                '<div class="right-sideabr">'.PHP_EOL.'                        @include',
                $html
            );
        }
    }

    public function test_account_pages_keep_existing_forms_and_photo_uploads(): void
    {
        $profile = $this->source('resources/views/frontend/pages/employer/update_profile.blade.php');
        $company = $this->source('resources/views/frontend/pages/employer/companyInformation.blade.php');
        $this->assertStringContainsString("route('employer-profile.update'", $profile);
        $this->assertStringContainsString('@method(\'put\')', $profile);
        $this->assertStringContainsString('enctype="multipart/form-data"', $profile);
        $this->assertStringContainsString('name="photo"', $profile);
        $this->assertStringContainsString("route('company.information.store')", $company);
        $this->assertStringContainsString('@csrf', $company);
        $this->assertStringContainsString("frontend.pages.employer.form", $company);
    }

    public function test_css_addresses_legacy_width_and_sidebar_title_conflicts(): void
    {
        $css = $this->source('public/theme/employer-account.css');
        $this->assertStringContainsString('.jg-employer-account .jg-employer-account-grid', $css);
        $this->assertStringContainsString('.jg-account-side', $css);
        $this->assertStringContainsString('width:auto!important', $css);
        $this->assertStringContainsString('.right-sideabr h4', $css);
        $this->assertStringContainsString('background:transparent!important', $css);
        $this->assertStringContainsString('grid-template-columns:minmax(0,1fr)!important', $css);
        $this->assertStringContainsString('@media(max-width:600px)', $css);
    }
}
