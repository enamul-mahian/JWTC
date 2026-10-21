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
        $this->middleware(['permission:application-setting'])->only('index', 'databaseBackup');
        $this->middleware(['permission:email-setting'])->only('email_setting');
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
        DB::beginTransaction();

        try {
            $general = gs();

            $general->mail_host = $request->mail_host;
            $general->mail_port = $request->mail_port;
            $general->mail_username = $request->mail_username;
            $general->mail_password = $request->mail_password;
            $general->mail_from_name = $request->mail_from_name;
            $general->mail_from_address = $request->mail_from_address;
            $general->mail_status = $request->mail_status;
            $general->mail_encryption = $request->mail_encryption;

            $general->save();

            $this->setEnv('MAIL_HOST', $request->mail_host);
            $this->setEnv('MAIL_PORT', $request->mail_port);
            $this->setEnv('MAIL_USERNAME', $request->mail_username);
            $this->setEnv('MAIL_PASSWORD', $request->mail_password);
            $this->setEnv('MAIL_FROM_ADDRESS', $request->mail_from_address);
            $this->setEnv('MAIL_FROM_NAME', $request->mail_from_name);
            $this->setEnv('MAIL_ENCRYPTION', $request->mail_encryption);

            Artisan::call('config:clear');

            DB::commit();

            return redirect()
                ->route('setting')
                ->with('success', 'Setting saved Successfully');

        } catch (\Exception $e) {

            DB::rollback();

            return redirect()
                ->route('setting')
                ->with('error', 'Something went wrong');
        }
    }

    private function setEnv($key, $value)
    {
        $envFilePath = app()->environmentFilePath();
        $envContents = File::get($envFilePath);

        $newEnvContents = preg_replace(
            "/^$key=.*/m",
            "$key=$value",
            $envContents
        );

        File::put($envFilePath, $newEnvContents);
    }

    /**
     * Send test email.
     */
    public function emailTesting()
    {
        try {

            Mail::to(gs()->business_email)->send(new TestEmail());

            return redirect()
                ->route('setting')
                ->with('success', 'Email send successfully');

        } catch (\Exception $e) {

            return redirect()
                ->route('setting')
                ->with('error', 'Error sending email: ' . $e->getMessage());
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