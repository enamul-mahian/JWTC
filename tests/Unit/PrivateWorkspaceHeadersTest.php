<?php
namespace Tests\Unit;
use App\Http\Middleware\PrivateDocumentResponseHeaders;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
class PrivateWorkspaceHeadersTest extends TestCase
{
    public function test_html_can_load_only_same_origin_theme_assets(): void
    {
        $r=(new PrivateDocumentResponseHeaders())->handle(Request::create('/job_seeker/interviews'),fn()=>new Response('<html></html>',200,['Content-Type'=>'text/html; charset=UTF-8']));
        $policy=$r->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline'",$policy);
        $this->assertStringContainsString("script-src 'self'",$policy);
        $this->assertStringContainsString("default-src 'none'",$policy);
        $this->assertStringContainsString("frame-ancestors 'none'",$policy);
        $this->assertStringNotContainsString('https:',$policy);
        $this->assertSame('DENY',$r->headers->get('X-Frame-Options'));
        $this->assertStringContainsString('no-store',$r->headers->get('Cache-Control'));
    }
    public function test_downloads_still_cannot_load_scripts_or_remote_styles(): void
    {
        $r=(new PrivateDocumentResponseHeaders())->handle(Request::create('/download'),fn()=>new Response('pdf',200,['Content-Type'=>'application/pdf']));
        $policy=$r->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString('script-src',$policy);
        $this->assertStringNotContainsString("style-src 'self'",$policy);
        $this->assertStringContainsString("default-src 'none'",$policy);
        $this->assertSame('nosniff',$r->headers->get('X-Content-Type-Options'));
    }
}
