# JWTC hCaptcha configuration

Implemented on the draft development branch (not yet merged into main or deployed).

## Protected entry points
- Browser login: `POST /user/login`
- Legacy employer/jobseeker registration: `POST /employer-register`
- Jobseeker referral registration: `POST /register`
- API login: `POST /api/login`
- API employer registration: `POST /api/registration`

The frontend widget sends `h-captcha-response`, and Laravel checks the token on the server against hCaptcha's `https://api.hcaptcha.com/siteverify` using an HTTPS, form-encoded POST. The verifier requires `success: true`; missing, invalid, expired and failed-network responses reject the action. hCaptcha response tokens cannot be reused.

## Private server configuration

Keep the keys exclusively in the server's **untracked** `.env` or your hosting environment settings:

```dotenv
HCAPTCHA_ENABLED=true
HCAPTCHA_SITEKEY=<site key from hCaptcha dashboard>
HCAPTCHA_SECRET=<secret key from hCaptcha dashboard>
```

**Never commit the secret**, enter it into chat logs, paste into shell command arguments, expose it through templates, or output the contents of the `.env` file. Rotate a key that has been shared outside a secure secret-management workflow before enabling the production service.

The application reads `config('services.hcaptcha.*')`. After changing the server environment, use `php artisan config:clear` in **staging only** and verify the login and registration flows before any live deployment. Do not edit the live `.env` or turn this on in production until reviewed.

If `HCAPTCHA_ENABLED=false` (default for backwards compatibility), these protected endpoints continue to function without a challenge. Once enabled, missing or invalid configuration fails closed. Enabling the challenge changes the API login/registration contract: API clients must submit a genuine browser-generated `h-captcha-response` token; do not automate solving or bypass verification.

## Deployment checks
1. Confirm the Site Key is configured for the **correct staging or production hostname** in the hCaptcha dashboard.
2. Test visible widget rendering on both registration pages and the web login.
3. Confirm empty, expired, reused or invalid tokens are rejected by the backend.
4. Confirm approved tokens allow correctly validated credentials or registration; all other account rules continue applying.
5. Verify API clients support the challenge and can handle a 422 validation error on failure.
6. Run PHPUnit tests and GitHub Actions; test DNS outbound access from the hosting server to hCaptcha.
7. Preserve existing records, production database, uploads, assets, URLs and analytics.

Official documentation: https://docs.hcaptcha.com/
