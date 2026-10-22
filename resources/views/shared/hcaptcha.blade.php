@if (config('services.hcaptcha.enabled'))
    <div class="my-3">
        @if (config('services.hcaptcha.sitekey'))
            <div class="h-captcha" data-sitekey="{{ config('services.hcaptcha.sitekey') }}"></div>
            @once
                <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
            @endonce
        @else
            <p class="text-danger" role="alert">Human verification is temporarily unavailable.</p>
        @endif
        @error('h-captcha-response')
            <p class="text-danger small mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>
@endif
