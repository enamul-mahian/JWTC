<?php

namespace Tests\Feature;

use App\Rules\ValidHcaptcha;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class HcaptchaVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.hcaptcha.enabled', true);
        config()->set('services.hcaptcha.sitekey', 'test-sitekey');
        config()->set('services.hcaptcha.secret', 'test-secret');
    }

    private function valid(string|null $token): bool
    {
        return Validator::make(
            ['h-captcha-response' => $token],
            ['h-captcha-response' => [
                Rule::requiredIf((bool) config('services.hcaptcha.enabled')),
                'nullable', 'string', 'max:8192', new ValidHcaptcha(),
            ]]
        )->passes();
    }

    public function test_missing_token_is_rejected_when_enabled(): void
    {
        Http::fake();
        $this->assertFalse($this->valid(null));
        Http::assertNothingSent();
    }

    public function test_rejected_token_stops_the_protected_action(): void
    {
        Http::fake(['https://api.hcaptcha.com/siteverify' => Http::response(['success' => false], 200)]);
        $this->assertFalse($this->valid('invalid-token'));
    }

    public function test_successful_verification_uses_form_post_and_expected_sitekey(): void
    {
        Http::fake(['https://api.hcaptcha.com/siteverify' => Http::response(['success' => true], 200)]);
        $this->assertTrue($this->valid('test-hcaptcha-token'));
        Http::assertSent(fn ($request) =>
            $request->method() === 'POST' &&
            $request->url() === 'https://api.hcaptcha.com/siteverify' &&
            $request['secret'] === 'test-secret' &&
            $request['sitekey'] === 'test-sitekey' &&
            $request['response'] === 'test-hcaptcha-token'
        );
    }

    public function test_network_error_or_unconfigured_secret_fails_closed(): void
    {
        Http::fake(['https://api.hcaptcha.com/siteverify' => Http::response('', 503)]);
        $this->assertFalse($this->valid('test-hcaptcha-token'));

        config()->set('services.hcaptcha.secret', null);
        $this->assertFalse($this->valid('test-hcaptcha-token'));
    }

    public function test_feature_flag_can_leave_unconfigured_staging_forms_unchanged(): void
    {
        config()->set('services.hcaptcha.enabled', false);
        Http::fake();
        $this->assertTrue($this->valid(null));
        Http::assertNothingSent();
    }
}
