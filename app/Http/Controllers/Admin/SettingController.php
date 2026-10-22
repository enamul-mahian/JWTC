<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Mail\TestEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Spatie\Backup\Tasks\Backup\Backup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Support\Renderable;
use Spatie\Backup\BackupDestination\BackupDestination;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:application-setting'])->only(['index', 'update', 'cachClear', 'databaseBackup']);
        $this->middleware(['permission:email-setting'])->only(['emailSetting', 'emailSettingUpdate', 'emailTesting']);
    }

    public function index()
    {
        $general = gs();

        return view('admin.setting.index', compact('general'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function emailSetting()
    {
        $general = gs();

        return view('admin.setting.email_seeting', compact('general'));
    }

    /**
     * Update email settings.
     */
    public function emailSettingUpdate(Request $request)
    {
        // A hosting environment file is not a writable application setting.
        // SMTP profiles for Email Studio remain isolated and encrypted.
        $data = $request->validate([
            'mail_host' => ['required', 'string', 'max:253', 'regex:/^[a-zA-Z0-9.-]+$/'],
            'mail_port' => ['required', 'integer', 'between:1,65535'],
            'mail_username' => ['required', 'string', 'max:254'],
            'mail_password' => ['nullable', 'string', 'max:1000'],
            'mail_from_name' => ['required', 'string', 'max:180'],
            'mail_from_address' => ['required', 'email:rfc', 'max:254'],
            'mail_status' => ['required', 'in:Active,Inactive'],
            'mail_encryption' => ['required', 'in:tls,ssl'],
        ]);

        try {
            DB::transaction(function () use ($data) {
                $general = gs();
                $general->mail_host = $data['mail_host'];
                $general->mail_port = (int) $data['mail_port'];
                $general->mail_username = $data['mail_username'];
                // An empty password means "keep existing", not "erase".
                if (!empty($data['mail_password'])) {
                    $general->mail_password = $data['mail_password'];
                }
                $general->mail_from_name = $data['mail_from_name'];
                $general->mail_from_address = $data['mail_from_address'];
                $general->mail_status = $data['mail_status'];
                $general->mail_encryption = $data['mail_encryption'];
                $general->save();
            });

            return redirect()->route('email-setting')
                ->with('success', 'SMTP configuration saved in the database. Hosting MAIL_* settings were not overwritten.');
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->route('email-setting')
                ->with('error', 'Unable to save email settings. Check the private server log.');
        }
    }

    /**
     * Test the saved DB SMTP profile without rewriting .env or the global
     * default mailer. This is an explicit authenticated POST action only.
     */
    public function emailTesting(Request $request)
    {
        $general = gs();
        if (($general->mail_status ?? '') !== 'Active') {
            return redirect()->route('email-setting')
                ->with('error', 'Activate a saved SMTP configuration before testing.');
        }

        $host = (string) ($general->mail_host ?? '');
        $port = (int) ($general->mail_port ?? 0);
        $recipient = (string) ($general->business_email ?? '');
        if (!preg_match('/^[a-zA-Z0-9.-]{1,253}$/', $host)
            || $port < 1 || $port > 65535
            || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('email-setting')
                ->with('error', 'Saved SMTP host, port or recipient is invalid.');
        }

        Config::set('mail.mailers.jwtc_legacy_test', [
            'transport' => 'smtp',
            'host' => $host,
            'port' => $port,
            'encryption' => (string) ($general->mail_encryption ?? 'tls'),
            'username' => (string) ($general->mail_username ?? ''),
            'password' => (string) ($general->mail_password ?? ''),
            'timeout' => 20,
            'local_domain' => config('mail.mailers.smtp.local_domain'),
        ]);

        try {
            Mail::purge('jwtc_legacy_test');
            Mail::mailer('jwtc_legacy_test')->to($recipient)->send(
                (new TestEmail())->from(
                    (string) ($general->mail_from_address ?? ''),
                    (string) ($general->mail_from_name ?? '')
                )
            );
            return redirect()->route('email-setting')
                ->with('success', 'SMTP accepted the test message. Verify inbox delivery.');
        } catch (\Throwable $exception) {
            report($exception);
            return redirect()->route('email-setting')
                ->with('error', 'SMTP connection or delivery failed. Check private logs.');
        } finally {
            Mail::purge('jwtc_legacy_test');
        }
    }

    /**
     * Database backup.
     */
    public function databaseBackup()
    {
        // Database exports contain sensitive applicant information. Never put
        // credentials on the command line or generate backups in public/.
        abort_unless(config('database.default') === 'mysql', 422, 'MySQL backup is not configured.');

        $config = config('database.connections.mysql');
        $database = (string) ($config['database'] ?? '');
        abort_if($database === '', 422, 'Database name is not configured.');

        $directory = storage_path('app/private/backups');
        File::ensureDirectoryExists($directory, 0700);

        $filename = 'database-' . gmdate('YmdHis') . '-' . bin2hex(random_bytes(8)) . '.sql';
        $filepath = $directory . DIRECTORY_SEPARATOR . $filename;

        $process = new \Symfony\Component\Process\Process([
            'mysqldump',
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--host=' . (string) ($config['host'] ?? '127.0.0.1'),
            '--port=' . (string) ($config['port'] ?? 3306),
            '--user=' . (string) ($config['username'] ?? ''),
            '--result-file=' . $filepath,
            $database,
        ]);
        $process->setEnv(['MYSQL_PWD' => (string) ($config['password'] ?? '')]);
        $process->setTimeout(300);

        try {
            $process->mustRun();

            if (!is_file($filepath) || filesize($filepath) === 0) {
                throw new \RuntimeException('Database export is empty.');
            }

            @chmod($filepath, 0600);

            return response()->download($filepath, $filename, [
                'Content-Type' => 'application/octet-stream',
                'Cache-Control' => 'private, no-store',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            if (is_file($filepath)) {
                @unlink($filepath);
            }

            report($e);
            return back()->withError('Database export failed. Check server configuration and logs.');
        }
    }

    /**
     * Update general application settings.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'business_name' => 'required',
            'business_address' => 'required',
            'business_number' => 'required',
            'business_email' => 'required|email',
            'currency_symbol' => 'required',

            'meta_title' => 'required',
            'meta_description' => 'required',

            // New Job Seeker Referral Code
            'job_seeker_referral_code' => [
                'required',
                'string',
                'min:4',
                'max:100',
            ],
        ]);

        if ($validator->fails()) {

            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {

            $general = gs();

            if ($request->hasFile('logo')) {

                $old = $general->logo;

                $logo = fileUploader(
                    $request->logo,
                    getFilePath('logo'),
                    getFileSize('logo'),
                    $old
                );

                $general->logo = $logo;
            }

            if ($request->hasFile('favicon')) {

                $old = $general->favicon;

                $favicon = fileUploader(
                    $request->favicon,
                    getFilePath('fav_icon'),
                    getFileSize('fav_icon'),
                    $old
                );

                $general->favicon = $favicon;
            }

            $general->business_name =
                $request->business_name;

            $general->business_address =
                $request->business_address;

            $general->business_number =
                $request->business_number;

            $general->business_email =
                $request->business_email;

            $general->currency_symbol =
                $request->currency_symbol;

            $general->meta_title =
                $request->meta_title;

            $general->meta_description =
                $request->meta_description;

            $general->address_line =
                $request->address_line;

            $general->phone_1 =
                $request->phone_1;

            $general->phone_2 =
                $request->phone_2;

            // New Job Seeker Referral Code
            $general->job_seeker_referral_code =
                trim($request->job_seeker_referral_code);

            $general->save();

            DB::commit();

            return redirect()
                ->route('setting')
                ->with(
                    'success',
                    'Data updated successfully'
                );

        } catch (\Exception $e) {

            DB::rollback();

            return redirect()
                ->route('setting')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function cachClear()
    {
        Artisan::call('cache:clear');

        return redirect()
            ->route('setting')
            ->with(
                'success',
                'Cache Clear Successfully'
            );
    }
}