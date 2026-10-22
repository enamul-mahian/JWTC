<?php

namespace App\Http\Controllers\Admin;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Mail\StudioEmail;
use App\Services\EmailStudioHtml;
use App\Services\StudioPdfVisuals;
use App\Services\StudioSmtpProfiles;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmailStudioController extends Controller
{
    private const SLOTS = ['top', 'bottom_left', 'bottom_right'];

    private function authorizeStudio(Request $request): void
    {
        $user = $request->user();
        abort_unless((bool) config('email_studio.enabled'), 404);
        abort_unless($user && in_array($user->user_type,
            [UserType::ADMIN, UserType::SUPER_ADMIN], true) &&
            $user->can('manage-email-studio'), 403);
        abort_unless(Schema::hasTable('email_studio_templates')
            && Schema::hasTable('email_studio_send_logs'), 503);
    }

    private function template(int $id): object
    {
        $template = DB::table('email_studio_templates')->where('id', $id)->first();
        abort_unless($template, 404);

        return $template;
    }

    public function index(Request $request)
    {
        $id = $request->query('template');
        if ($id !== null && (!ctype_digit((string) $id) || (int) $id < 1)) {
            abort(404);
        }
        return $this->edit($request, $id !== null ? (int) $id : null);
    }

    public function edit(Request $request, ?int $id = null)
    {
        $this->authorizeStudio($request);
        $template = $id === null ? null : $this->template($id);
        $images = $template ? (json_decode($template->images ?: '{}', true) ?: []) : [];
        $profiles = Schema::hasTable('email_studio_smtp_profiles')
            ? app(StudioSmtpProfiles::class)->all() : collect();
        $visualPages = $template && Schema::hasTable('email_studio_visual_pages')
            ? DB::table('email_studio_visual_pages')
                ->where('template_id', $template->id)->orderBy('page_number')->get()
            : collect();

        return view('email-studio.editor', [
            'template' => $template,
            'images' => $images,
            'templates' => DB::table('email_studio_templates')
                ->orderBy('name')->get(['id', 'name', 'subject']),
            'profiles' => $profiles,
            'defaultProfileId' => Schema::hasTable('email_studio_smtp_profiles')
                ? app(StudioSmtpProfiles::class)->defaultId() : null,
            'requestedSmtpProfile' => $request->query('smtp_profile'),
            'logs' => DB::table('email_studio_send_logs')
                ->where('actor_id', $request->user()->id)
                ->orderByDesc('id')->limit(25)->get(),
            'visualPages' => $visualPages,
            'availableFonts' => [
                'Arial', 'Georgia', 'Verdana', 'Tahoma', 'Helvetica', 'Times New Roman'
            ],
        ]);
    }

    public function save(Request $request, EmailStudioHtml $formatter, ?int $id = null)
    {
        $this->authorizeStudio($request);

        $rules = [
            'name' => ['required', 'string', 'max:180'],
            'subject' => ['required', 'string', 'max:250'],
            'html_body' => ['required', 'string', 'max:' . config('email_studio.max_body_chars', 40000)],
            'footer' => ['nullable', 'string', 'max:2000'],
        ];
        foreach (self::SLOTS as $slot) {
            $rules['image_' . $slot] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp',
                'max:' . config('email_studio.max_image_kb', 2048)];
            $rules['width_' . $slot] = ['required', 'integer', 'between:80,600'];
            $rules['delete_' . $slot] = ['nullable', 'boolean'];
        }

        $data = $request->validate($rules);
        $clean = trim($formatter->sanitize($data['html_body']));

        if ($formatter->plainText($clean) === '') {
            throw ValidationException::withMessages([
                'html_body' => 'The message body must contain readable text.',
            ]);
        }

        if ($id !== null) {
            $template = $this->template($id);
            $images = json_decode($template->images ?: '{}', true) ?: [];
        } else {
            abort_if(DB::table('email_studio_templates')->count() >=
                config('email_studio.max_templates', 200), 422, 'Template limit reached.');
            $images = [];
        }

        $now = now();
        $fields = [
            'name' => $data['name'], 'subject' => $data['subject'],
            'html_body' => $clean, 'footer' => $data['footer'] ?? '',
            'updated_by' => $request->user()->id, 'updated_at' => $now,
        ];

        if ($id === null) {
            $id = DB::table('email_studio_templates')->insertGetId($fields + [
                'created_by' => $request->user()->id, 'created_at' => $now,
            ]);
        }

        $oldPaths = [];
        foreach (self::SLOTS as $slot) {
            $width = (int) $data['width_' . $slot];
            $previous = $images[$slot]['path'] ?? null;

            if ($request->boolean('delete_' . $slot)) {
                unset($images[$slot]);
                if ($previous) {
                    $oldPaths[] = $previous;
                }
                continue;
            }

            if ($request->hasFile('image_' . $slot)) {
                $newPath = $request->file('image_' . $slot)->store(
                    'private/email-studio/' . $id . '/' . $slot, 'local'
                );
                if (!$newPath) {
                    throw ValidationException::withMessages([
                        'image_' . $slot => 'Could not store image.',
                    ]);
                }
                $images[$slot] = ['path' => $newPath, 'width' => $width];
                if ($previous) {
                    $oldPaths[] = $previous;
                }
            } elseif (isset($images[$slot])) {
                $images[$slot]['width'] = $width;
            }
        }

        DB::table('email_studio_templates')->where('id', $id)->update(
            $fields + ['images' => json_encode($images, JSON_THROW_ON_ERROR)]
        );

        // Existing files are deleted only after DB references change.
        foreach ($oldPaths as $path) {
            if (str_starts_with((string) $path, 'private/email-studio/')) {
                Storage::disk('local')->delete($path);
            }
        }

        return redirect()->route('email-studio.edit', $id)
            ->with('success', 'Email template saved privately.');
    }

    public function image(Request $request, int $id, string $slot)
    {
        $this->authorizeStudio($request);
        abort_unless(in_array($slot, self::SLOTS, true), 404);
        $template = $this->template($id);
        $images = json_decode($template->images ?: '{}', true) ?: [];
        $path = $images[$slot]['path'] ?? null;
        abort_unless(is_string($path) &&
            str_starts_with($path, 'private/email-studio/' . $id . '/' . $slot . '/') &&
            Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function send(Request $request, EmailStudioHtml $formatter, int $id)
    {
        $this->authorizeStudio($request);
        $data = $request->validate([
            'recipient' => ['required', 'email:rfc', 'max:254'],
            'client_name' => ['nullable', 'string', 'max:180'],
            'reference' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:150'],
            'message' => ['nullable', 'string', 'max:4000'],
            'smtp_profile_id' => ['nullable', 'integer', 'min:1'],
            'pdf_mode' => ['required', Rule::in(['none', 'upload', 'generate'])],
            'pdf' => ['required_if:pdf_mode,upload', 'nullable', 'file',
                'mimes:pdf', 'max:' . config('email_studio.max_pdf_kb', 8192)],
        ]);

        $chosenProfile = !empty($data['smtp_profile_id'])
            ? (int) $data['smtp_profile_id']
            : app(StudioSmtpProfiles::class)->defaultId();
        $smtp = app(StudioSmtpProfiles::class)->settings($chosenProfile);
        abort_unless(filter_var($smtp['from_address'], FILTER_VALIDATE_EMAIL), 503,
            'The SMTP sender address is not configured.');

        $template = $this->template($id);
        $values = [
            'client_name' => $data['client_name'] ?? '',
            'reference' => $data['reference'] ?? '',
            'status' => $data['status'] ?? '',
            'message' => $data['message'] ?? '',
            'date' => now()->format('d M Y'),
        ];

        $body = $formatter->personalize(
            $formatter->sanitize($template->html_body), $values
        );
        $subject = str_replace(["\r", "\n"], ' ', $formatter->personalize(
            $template->subject, $values, false
        ));
        $footer = $formatter->personalize($template->footer ?: '', $values, false);
        $images = json_decode($template->images ?: '{}', true) ?: [];
        $pdfBytes = null;
        $pdfName = null;

        if ($data['pdf_mode'] === 'upload') {
            $pdfBytes = file_get_contents($data['pdf']->getRealPath());
            $pdfName = 'attachment.pdf'; // Avoid untrusted names in mail headers.
        } elseif ($data['pdf_mode'] === 'generate') {
            $pdfImages = [];
            foreach (self::SLOTS as $slot) {
                $path = $images[$slot]['path'] ?? null;
                if (!$path || !str_starts_with($path, 'private/email-studio/')
                    || !Storage::disk('local')->exists($path)) {
                    continue;
                }
                $mime = Storage::disk('local')->mimeType($path);
                if (!in_array($mime, ['image/png', 'image/jpeg'], true)) {
                    continue; // PDF image format support varies; skip WebP.
                }
                $pdfImages[$slot] = 'data:' . $mime . ';base64,' .
                    base64_encode(Storage::disk('local')->get($path));
            }

            $pdfBytes = Pdf::loadView('emails.studio_pdf', [
                'bodyHtml' => $body,
                'footerText' => $footer,
                'pdfImages' => $pdfImages,
            ])->setPaper('a4')->setOption('isRemoteEnabled', false)->output();
            $pdfName = 'JWTC-document.pdf';
        }

        $result = 'failed';
        try {
            // Explicit SMTP mailer: do not fall back to the "log" driver and
            // report a false positive when the outbound connection is broken.
            $visualPages = Schema::hasTable('email_studio_visual_pages')
                ? DB::table('email_studio_visual_pages')->where('template_id', $id)
                    ->orderBy('page_number')->get()->map(fn ($row) => [
                        'page_number' => $row->page_number,
                        'private_path' => $row->private_path,
                    ])->all() : [];
            Mail::mailer($smtp['mailer'])->to($data['recipient'])->send(new StudioEmail(
                $subject, $body, $formatter->plainText($body),
                $footer, $images, $pdfBytes, $pdfName,
                $visualPages, $smtp['from_address'], $smtp['from_name']
            ));
            $result = 'accepted'; // SMTP accepted does NOT guarantee Gmail inbox.
        } catch (\Throwable $exception) {
            report($exception);
        } finally {
            DB::table('email_studio_send_logs')->insert([
                'template_id' => $template->id,
                'actor_id' => $request->user()->id,
                'recipient' => $data['recipient'],
                'subject' => $subject,
                'result' => $result,
                'pdf_attached' => $pdfBytes !== null,
                'created_at' => now(),
            ]);
        }

        if ($result !== 'accepted') {
            return back()->withErrors(['recipient' =>
                'The SMTP send failed. Check the private server mail logs and settings.']);
        }

        return back()->with('success',
            'The configured SMTP server accepted the message. Check the recipient inbox.');
    }
}
