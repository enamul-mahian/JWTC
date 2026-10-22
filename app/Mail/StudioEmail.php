<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Storage;

class StudioEmail extends Mailable
{
    public function __construct(
        public string $subjectLine,
        public string $bodyHtml,
        public string $bodyText,
        public string $footerText,
        public array $images = [],
        public ?string $pdfBytes = null,
        public ?string $pdfFilename = null
    ) {
    }

    public function build(): static
    {
        $inline = [];
        foreach (['top', 'bottom_left', 'bottom_right'] as $slot) {
            $image = $this->images[$slot] ?? null;
            if (is_array($image) && isset($image['path'], $image['width'])
                && str_starts_with($image['path'], 'private/email-studio/')
                && Storage::disk('local')->exists($image['path'])) {
                $inline[$slot] = [
                    'absolute_path' => Storage::disk('local')->path($image['path']),
                    'width' => min(600, max(80, (int) $image['width'])),
                ];
            }
        }

        $this->from(
            (string) config('email_studio.from_address'),
            (string) config('email_studio.from_name')
        )->subject($this->subjectLine)
            ->view('emails.studio_html', ['inlineImages' => $inline])
            ->text('emails.studio_text');

        if ($this->pdfBytes !== null) {
            $name = $this->pdfFilename ?: 'document.pdf';
            $this->attachData($this->pdfBytes, $name, ['mime' => 'application/pdf']);
        }

        return $this;
    }
}
