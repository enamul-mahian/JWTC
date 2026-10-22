<?php

namespace App\Http\Controllers\Admin;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Mail\StudioSmtpTestMail;
use App\Services\StudioSmtpProfiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class EmailStudioSmtpController extends Controller
{
    private function authorized(Request $request): void
    {
        abort_unless(config('email_studio.enabled'), 404);
        $user = $request->user();
        abort_unless($user && in_array($user->user_type, [
            UserType::ADMIN, UserType::SUPER_ADMIN
        ], true) && $user->can('manage-email-studio'), 403);
        abort_unless(Schema::hasTable('email_studio_smtp_profiles'), 503);
    }

    private function rules(bool $existing): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'host' => ['required', 'string', 'max:253'],
            'port' => ['required', 'integer', Rule::in([465,587])],
            'encryption' => ['required', Rule::in(['ssl','tls'])],
            'username' => ['required', 'string', 'max:254'],
            'password' => [$existing ? 'nullable' : 'required', 'string', 'max:2000'],
            'from_address' => ['required','email:rfc','max:254'],
            'from_name' => ['required','string','max:180'],
        ];
    }

    public function save(Request $request, StudioSmtpProfiles $smtp, ?int $id = null)
    {
        $this->authorized($request);
        $data = $request->validate($this->rules($id !== null));
        $smtp->validateServer($data['host'], (int) $data['port'], $data['encryption']);

        $old = $id === null ? null : DB::table('email_studio_smtp_profiles')
            ->where('id', $id)->first();
        if ($id !== null) {
            abort_unless($old, 404);
        }

        $data['host'] = strtolower(trim($data['host']));
        $data['port'] = (int) $data['port'];
        if (!empty($data['password'])) {
            $data['password_encrypted'] = Crypt::encryptString($data['password']);
        }
        unset($data['password']);
        $data['updated_by'] = $request->user()->id;
        $data['updated_at'] = now();

        if ($old) {
            DB::table('email_studio_smtp_profiles')->where('id', $id)->update($data);
        } else {
            $id = DB::table('email_studio_smtp_profiles')->insertGetId(
                $data + ['is_default' => false, 'created_at' => now()]
            );
        }

        return redirect()->route('email-studio.index', ['smtp_profile' => $id])
            ->with('success', 'SMTP profile saved. The password is encrypted and never displayed.');
    }

    public function makeDefault(Request $request, int $id)
    {
        $this->authorized($request);
        DB::transaction(function () use ($id) {
            DB::table('email_studio_smtp_profiles')->lockForUpdate()->get(['id']);
            $found = DB::table('email_studio_smtp_profiles')->where('id', $id)->exists();
            abort_unless($found, 404);
            DB::table('email_studio_smtp_profiles')->update(['is_default' => false]);
            DB::table('email_studio_smtp_profiles')
                ->where('id', $id)->update(['is_default' => true]);
        }, 3);

        return redirect()->route('email-studio.index', ['smtp_profile' => $id])
            ->with('success', 'This SMTP profile is now the Studio default.');
    }

    public function remove(Request $request, int $id)
    {
        $this->authorized($request);
        DB::transaction(function () use ($id) {
            $profile = DB::table('email_studio_smtp_profiles')
                ->where('id', $id)->lockForUpdate()->first();
            abort_unless($profile, 404);
            DB::table('email_studio_smtp_profiles')->where('id', $id)->delete();
        }, 3);

        return redirect()->route('email-studio.index')
            ->with('success', 'SMTP profile deleted. Old send logs remain available.');
    }

    public function test(Request $request, StudioSmtpProfiles $profiles, int $id)
    {
        $this->authorized($request);
        $settings = $profiles->settings($id);
        $recipient = (string) $request->user()->email;
        abort_unless(filter_var($recipient, FILTER_VALIDATE_EMAIL), 422);

        $result = 'failed';
        try {
            Mail::mailer($settings['mailer'])->to($recipient)->send(
                (new StudioSmtpTestMail())->from(
                    $settings['from_address'], $settings['from_name']
                )
            );
            $result = 'accepted';
        } catch (\Throwable $exception) {
            report($exception);
        }

        DB::table('email_studio_send_logs')->insert([
            'template_id' => null, 'actor_id' => $request->user()->id,
            'recipient' => $recipient, 'subject' => 'JWTC SMTP Profile Connection Test',
            'result' => $result, 'pdf_attached' => false,
            'created_at' => now(),
        ]);

        return redirect()->route('email-studio.index', ['smtp_profile' => $id])
            ->with($result === 'accepted' ? 'success' : 'smtp_error',
                $result === 'accepted'
                ? 'SMTP accepted the test message. Check your inbox and Gmail Show Original.'
                : 'SMTP connection or authentication failed. Inspect secure server logs.');
    }
}
