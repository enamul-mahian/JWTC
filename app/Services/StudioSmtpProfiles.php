<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class StudioSmtpProfiles
{
    public function all()
    {
        // Do not select password_encrypted for any dashboard view.
        return DB::table('email_studio_smtp_profiles')
            ->orderByDesc('is_default')->orderBy('name')
            ->get(['id','name','host','port','encryption','username',
                'from_address','from_name','is_default','updated_at']);
    }

    public function settings(?int $id = null): array
    {
        if ($id === null) {
            $settings = config('mail.mailers.studio_smtp');
            abort_unless(is_array($settings) && !empty($settings['host'])
                && !empty($settings['username']), 503,
                'Environment SMTP server is not configured.');

            return [
                'mailer' => 'studio_smtp',
                'from_address' => (string) config('email_studio.from_address'),
                'from_name' => (string) config('email_studio.from_name'),
            ];
        }

        $profile = DB::table('email_studio_smtp_profiles')->where('id', $id)->first();
        abort_unless($profile, 404);
        $settings = [
            'transport' => 'smtp',
            'host' => $profile->host,
            'port' => (int) $profile->port,
            'encryption' => $profile->encryption === 'ssl' ? 'ssl' : 'tls',
            'username' => $profile->username,
            'password' => Crypt::decryptString($profile->password_encrypted),
            'timeout' => 20,
            'local_domain' => config('mail.mailers.studio_smtp.local_domain'),
        ];

        // This dynamic named mailer exists for this request only. It is never
        // written into .env or config/mail.php and won't affect regular mail.
        config()->set('mail.mailers.studio_profile_runtime', $settings);
        Mail::purge('studio_profile_runtime');

        return [
            'mailer' => 'studio_profile_runtime',
            'from_address' => $profile->from_address,
            'from_name' => $profile->from_name,
        ];
    }

    public function defaultId(): ?int
    {
        if (!Schema::hasTable('email_studio_smtp_profiles')) return null;
        $id = DB::table('email_studio_smtp_profiles')
            ->where('is_default', true)->value('id');
        return $id === null ? null : (int) $id;
    }

    public function validateServer(string $host, int $port, string $encryption): void
    {
        $name = strtolower(trim($host));
        // Privileged outbound network target: reject private IP, localhost,
        // Unicode control chars and unsanctioned ports.
        if (strlen($name) > 253 || !preg_match('/^(?=.{4,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $name)
            || preg_match('/(?:^|\.)((?:localhost)|(?:local)|(?:internal)|(?:test)|(?:invalid))$/', $name)
            || !in_array($port, [465, 587], true)
            || ($port === 465 && $encryption !== 'ssl')
            || ($port === 587 && $encryption !== 'tls')) {
            throw ValidationException::withMessages([
                'host' => 'Use a public SMTP hostname with TLS on port 587 or SSL/TLS on port 465.',
            ]);
        }
    }
}
