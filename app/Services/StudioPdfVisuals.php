<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Process;

class StudioPdfVisuals
{
    /**
     * Convert a private PDF into 1–3 width-limited PNGs without shell interpolation.
     * Requires the staging host's reviewed pdfinfo + pdftoppm binaries.
     */
    public function fromPdf(UploadedFile $file, int $templateId): array
    {
        $temp = 'private/email-studio/' . $templateId . '/import/' . Str::uuid();
        $disk = Storage::disk('local');
        $disk->makeDirectory($temp);
        $originalPath = $temp . '/source.pdf';

        try {
            $content = file_get_contents($file->getRealPath());
            if ($content === false || !str_starts_with($content, '%PDF-')) {
                throw ValidationException::withMessages([
                    'pdf_visual' => 'The uploaded file is not a valid PDF.',
                ]);
            }
            $disk->put($originalPath, $content);
            unset($content);

            $info = new Process(['pdfinfo', $disk->path($originalPath)]);
            $info->setTimeout(12);
            $info->run();
            if (!$info->isSuccessful() || !preg_match('/^Pages:\s*(\d+)\s*$/mi',
                $info->getOutput(), $found)) {
                throw ValidationException::withMessages([
                    'pdf_visual' => 'PDF rendering is not available or this PDF is invalid on the server.',
                ]);
            }
            $count = (int) $found[1];
            if ($count < 1 || $count > 3) {
                throw ValidationException::withMessages([
                    'pdf_visual' => 'Upload a PDF containing 1 to 3 pages.',
                ]);
            }

            $pages = [];
            for ($page = 1; $page <= $count; $page++) {
                $prefix = $disk->path($temp . '/page-' . $page);
                $process = new Process([
                    'pdftoppm', '-f', (string)$page, '-l', (string)$page,
                    '-scale-to', '1200', '-png', '-singlefile',
                    $disk->path($originalPath), $prefix
                ]);
                $process->setTimeout(18);
                $process->run();

                $rendered = $prefix . '.png';
                if (!$process->isSuccessful() || !is_file($rendered)
                    || filesize($rendered) > 3 * 1024 * 1024
                    || filesize($rendered) === 0) {
                    throw ValidationException::withMessages([
                        'pdf_visual' => 'PDF conversion failed or produced an oversized page.',
                    ]);
                }
                $this->ensureImage($rendered);
                $pages[] = $this->saveImage($rendered, $templateId, $page);
            }

            return $pages;
        } catch (ValidationException $e) {
            $this->deletePageFiles($pages ?? []);
            throw $e;
        } catch (\Throwable $error) {
            $this->deletePageFiles($pages ?? []);
            throw ValidationException::withMessages([
                'pdf_visual' => 'PDF converter unavailable. Upload approved PNG/JPG page images instead.',
            ]);
        } finally {
            $disk->deleteDirectory($temp);
        }
    }

    public function fromImages(array $files, int $templateId): array
    {
        $pages = [];
        try {
            foreach ([1, 2, 3] as $page) {
                $file = $files[$page] ?? null;
                if (!$file instanceof UploadedFile) {
                    continue;
                }
                $this->ensureImage($file->getRealPath());
                $pages[] = $this->saveImage($file->getRealPath(), $templateId, $page);
            }
        } catch (\Throwable $error) {
            $this->deletePageFiles($pages);
            throw $error;
        }

        if (!$pages) {
            throw ValidationException::withMessages(['pdf_visual' => 'Choose a PDF or at least one approved page image.']);
        }
        return $pages;
    }

    public function deletePageFiles(array $pages): void
    {
        $disk = Storage::disk('local');
        foreach ($pages as $page) {
            $path = is_array($page) ? ($page['private_path'] ?? null)
                : ($page->private_path ?? null);
            if (is_string($path) && str_starts_with($path, 'private/email-studio/')) {
                $disk->delete($path);
            }
        }
    }

    private function ensureImage(string $path): void
    {
        $mime = @mime_content_type($path);
        $info = @getimagesize($path);
        if (!in_array($mime, ['image/png', 'image/jpeg'], true)
            || !is_array($info) || $info[0] < 1 || $info[1] < 1
            || $info[0] > 6000 || $info[1] > 10000) {
            throw ValidationException::withMessages([
                'pdf_visual' => 'Only valid PNG/JPEG page images are supported.',
            ]);
        }
    }

    private function saveImage(string $sourcePath, int $templateId, int $page): array
    {
        $bytes = file_get_contents($sourcePath);
        if ($bytes === false || strlen($bytes) > 3 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'pdf_visual' => 'A page is too large. Reduce the image resolution.',
            ]);
        }
        $sha = hash('sha256', $bytes);
        $mime = mime_content_type($sourcePath);
        $ext = $mime === 'image/png' ? 'png' : 'jpg';
        $path = 'private/email-studio/' . $templateId . '/pdf-visual/' .
            Str::uuid() . '.' . $ext;
        if (!Storage::disk('local')->put($path, $bytes)) {
            throw ValidationException::withMessages([
                'pdf_visual' => 'Could not store a converted page privately.',
            ]);
        }
        return [
            'page_number' => $page, 'private_path' => $path,
            'file_size' => strlen($bytes), 'sha256' => $sha,
            'created_at' => now(),
        ];
    }
}
