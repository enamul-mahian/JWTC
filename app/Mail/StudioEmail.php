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
        public ?string $pdfFilename = null,
        public array $visualPages = [],
        public ?string $senderAddress = null,
        public ?string $senderName = null
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
                    'align' => in_array($image['align'] ?? 'center', ['left','center','right'], true)
                        ? ($image['align'] ?? 'center') : 'center',
                ];
            }
        }

        $cidPages = [];
        foreach ($this->visualPages as $page) {
            if (!isset($page['private_path'], $page['page_number'])) continue;
            $path = $page['private_path'];
            if (is_string($path) && preg_match(
                '#^private/email-studio/[0-9]+/pdf-visual/[a-f0-9-]+\.(png|jpg)$#i', $path)
                && Storage::disk('local')->exists($path)) {
                $cidPages[] = [
                    'page_number' => (int) $page['page_number'],
                    'absolute_path' => Storage::disk('local')->path($path),
                ];
            }
        }

        $this->from(
            $this->senderAddress ?: (string) config('email_studio.from_address'),
            $this->senderName ?: (string) config('email_studio.from_name')
        )->subject($this->subjectLine)
            ->view('emails.studio_html', [
                'inlineImages' => $inline,
                'inlineVisualPages' => $cidPages,
            ])
            ->text('emails.studio_text');

        if ($this->pdfBytes !== null) {
            $name = $this->pdfFilename ?: 'document.pdf';
            $this->attachData($this->pdfBytes, $name, ['mime' => 'application/pdf']);
        }

        return $this;
    }
}
