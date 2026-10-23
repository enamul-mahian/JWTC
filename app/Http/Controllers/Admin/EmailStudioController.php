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
            ($user->can('manage-email-studio') || $user->hasAnyRole(['Admin','Super Admin'])), 403);
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
        $this->authorizeStudio($request);
        $selected = $id !== null ? (int) $id
            : DB::table('email_studio_templates')->orderByDesc('updated_at')->value('id');
        return $this->edit($request, $selected ? (int) $selected : null);
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
            $rules['align_' . $slot] = ['sometimes', 'nullable', Rule::in(['left','center','right'])];
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
            $align = $data['align_' . $slot] ?? ($images[$slot]['align'] ?? 'center');
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
                $images[$slot] = ['path' => $newPath, 'width' => $width, 'align' => $align];
                if ($previous) {
                    $oldPaths[] = $previous;
                }
            } elseif (isset($images[$slot])) {
                $images[$slot]['width'] = $width;
                $images[$slot]['align'] = $align;
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
            'send_subject' => ['nullable', 'string', 'max:250'],
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
        if (!empty($data['send_subject'])) {
            $subject = str_replace(["\r", "\n"], ' ',
                $formatter->personalize($data['send_subject'], $values, false));
        }
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
    public function duplicate(Request $request, int $id)
    {
        $this->authorizeStudio($request);
        $original = $this->template($id);
        abort_if(DB::table('email_studio_templates')->count() >=
            config('email_studio.max_templates', 200), 422);

        $stored = [];
        try {
            $newId = DB::transaction(function () use ($request, $original, $id, &$stored) {
                $now = now();
                $newId = DB::table('email_studio_templates')->insertGetId([
                    'name' => mb_substr($original->name . ' (Copy)', 0, 180),
                    'subject' => $original->subject,
                    'html_body' => $original->html_body,
                    'footer' => $original->footer,
                    'images' => '{}', 'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $oldImages = json_decode($original->images ?: '{}', true) ?: [];
                $copies = [];
                foreach (self::SLOTS as $slot) {
                    $img = $oldImages[$slot] ?? null;
                    $path = $img['path'] ?? null;
                    if (!$path || !str_starts_with($path,
                        'private/email-studio/' . $id . '/' . $slot . '/')) continue;
                    $newPath = 'private/email-studio/' . $newId . '/' . $slot .
                        '/' . \Illuminate\Support\Str::uuid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
                    if (!Storage::disk('local')->copy($path, $newPath)) {
                        throw new \RuntimeException('Image copy failed.');
                    }
                    $stored[] = $newPath;
                    $copies[$slot] = [
                        'path' => $newPath,
                        'width' => $img['width'] ?? 250,
                        'align' => $img['align'] ?? 'center',
                    ];
                }
                DB::table('email_studio_templates')->where('id', $newId)
                    ->update(['images' => json_encode($copies, JSON_THROW_ON_ERROR)]);

                if (Schema::hasTable('email_studio_visual_pages')) {
                    $pages = DB::table('email_studio_visual_pages')
                        ->where('template_id', $id)->orderBy('page_number')->get();
                    foreach ($pages as $page) {
                        $newPath = 'private/email-studio/' . $newId . '/pdf-visual/' .
                            \Illuminate\Support\Str::uuid() . '.' .
                            pathinfo($page->private_path, PATHINFO_EXTENSION);
                        if (!Storage::disk('local')->copy($page->private_path, $newPath)) {
                            throw new \RuntimeException('Page image copy failed.');
                        }
                        $stored[] = $newPath;
                        DB::table('email_studio_visual_pages')->insert([
                            'template_id' => $newId,
                            'page_number' => $page->page_number,
                            'private_path' => $newPath,
                            'file_size' => $page->file_size,
                            'sha256' => $page->sha256,
                            'created_at' => now(),
                        ]);
                    }
                }
                return $newId;
            }, 3);
        } catch (\Throwable $error) {
            foreach ($stored as $path) Storage::disk('local')->delete($path);
            report($error);
            throw ValidationException::withMessages([
                'template' => 'The template could not be duplicated. No changes were saved.',
            ]);
        }

        return redirect()->route('email-studio.edit', $newId)
            ->with('success', 'Template duplicated with separate private image files.');
    }

    public function destroy(Request $request, int $id)
    {
        $this->authorizeStudio($request);
        $original = $this->template($id);
        $images = json_decode($original->images ?: '{}', true) ?: [];
        $paths = [];
        foreach ($images as $img) {
            $path = $img['path'] ?? null;
            if (is_string($path) && str_starts_with($path,
                'private/email-studio/' . $id . '/')) $paths[] = $path;
        }
        if (Schema::hasTable('email_studio_visual_pages')) {
            $paths = array_merge($paths, DB::table('email_studio_visual_pages')
                ->where('template_id', $id)->pluck('private_path')->all());
        }
        DB::transaction(function () use ($id) {
            DB::table('email_studio_send_logs')->where('template_id', $id)
                ->update(['template_id' => null]);
            if (Schema::hasTable('email_studio_visual_pages')) {
                DB::table('email_studio_visual_pages')->where('template_id', $id)->delete();
            }
            DB::table('email_studio_templates')->where('id', $id)->delete();
        }, 3);

        foreach ($paths as $path) {
            if (str_starts_with((string) $path, 'private/email-studio/' . $id . '/')) {
                Storage::disk('local')->delete($path);
            }
        }
        return redirect()->route('email-studio.index')
            ->with('success', 'Template deleted. Historical SMTP attempt logs remain.');
    }

    public function visualPage(Request $request, int $id, int $page)
    {
        $this->authorizeStudio($request);
        $this->template($id);
        abort_unless($page >= 1 && $page <= 3 &&
            Schema::hasTable('email_studio_visual_pages'), 404);
        $record = DB::table('email_studio_visual_pages')
            ->where('template_id', $id)->where('page_number', $page)->first();
        abort_unless($record && str_starts_with($record->private_path,
            'private/email-studio/' . $id . '/pdf-visual/') &&
            Storage::disk('local')->exists($record->private_path), 404);

        return Storage::disk('local')->response($record->private_path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function importVisual(Request $request, StudioPdfVisuals $visuals, int $id)
    {
        $this->authorizeStudio($request);
        $this->template($id);
        abort_unless(Schema::hasTable('email_studio_visual_pages'), 503);

        $data = $request->validate([
            'pdf_visual' => ['nullable','file','mimes:pdf','max:8192'],
            'visual_page_1' => ['nullable','file','mimes:png,jpg,jpeg','max:3072'],
            'visual_page_2' => ['nullable','file','mimes:png,jpg,jpeg','max:3072'],
            'visual_page_3' => ['nullable','file','mimes:png,jpg,jpeg','max:3072'],
        ]);
        if ($request->hasFile('pdf_visual') && (
            $request->hasFile('visual_page_1') || $request->hasFile('visual_page_2')
            || $request->hasFile('visual_page_3'))) {
            throw ValidationException::withMessages([
                'pdf_visual' => 'Choose either a PDF or individual page images.',
            ]);
        }

        $new = $request->hasFile('pdf_visual')
            ? $visuals->fromPdf($data['pdf_visual'], $id)
            : $visuals->fromImages([
                1 => $data['visual_page_1'] ?? null,
                2 => $data['visual_page_2'] ?? null,
                3 => $data['visual_page_3'] ?? null,
            ], $id);

        $old = DB::table('email_studio_visual_pages')
            ->where('template_id', $id)->get()->all();
        try {
            DB::transaction(function () use ($new, $id) {
                DB::table('email_studio_visual_pages')->where('template_id', $id)->delete();
                foreach ($new as $row) {
                    DB::table('email_studio_visual_pages')
                        ->insert(['template_id' => $id] + $row);
                }
            }, 3);
        } catch (\Throwable $error) {
            $visuals->deletePageFiles($new);
            throw $error;
        }

        $visuals->deletePageFiles($old);
        return redirect()->route('email-studio.edit', $id)
            ->with('success', 'Visual pages imported and ready for CID email embedding.');
    }

    public function removeVisual(Request $request, StudioPdfVisuals $visuals, int $id)
    {
        $this->authorizeStudio($request);
        $this->template($id);
        abort_unless(Schema::hasTable('email_studio_visual_pages'), 503);
        $old = DB::table('email_studio_visual_pages')
            ->where('template_id', $id)->get()->all();

        DB::table('email_studio_visual_pages')->where('template_id', $id)->delete();
        $visuals->deletePageFiles($old);
        return redirect()->route('email-studio.edit', $id)
            ->with('success', 'Imported visual pages removed from this template.');
    }

}
