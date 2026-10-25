<?php

namespace App\Services;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

/**
 * Manual, administrator-initiated Work Hub import. Drafts only: never publishes jobs.
 * No arbitrary remote URLs or redirect following.
 */
final class WorkHubLinkImporter
{
    private const ORIGIN = 'https://www.jobs.service.gov.uk';
    private const MAX_SEARCH_LISTINGS = 10;

    /**
     * Only allow HTTPS Work Hub search URLs and 24-character job IDs.
     * Return a normalized, canonical same-host URL and its type.
     */
    public function normalizeUrl(string $input): array
    {
        $input = trim($input);
        if (strlen($input) > 2048 || strlen($input) < 25 || preg_match('/[\x00-\x20\x7f]/', $input)) {
            throw new InvalidArgumentException('Paste a valid HTTPS jobs.service.gov.uk link.');
        }
        $url = parse_url($input);
        if (!is_array($url) || strtolower((string) ($url['scheme'] ?? '')) !== 'https'
            || !in_array(strtolower((string) ($url['host'] ?? '')), ['jobs.service.gov.uk', 'www.jobs.service.gov.uk'], true)
            || isset($url['user']) || isset($url['pass']) || isset($url['port']) || isset($url['fragment'])) {
            throw new InvalidArgumentException('Only HTTPS jobs.service.gov.uk links are supported.');
        }
        $path = (string) ($url['path'] ?? '');
        if (preg_match('~^/jobs/([a-f0-9]{24})/?$~i', $path, $matches)) {
            return ['type' => 'job', 'url' => self::ORIGIN.'/jobs/'.strtolower($matches[1])];
        }
        if ($path === '/jobs/search' || $path === '/jobs/search/') {
            $query = (string) ($url['query'] ?? '');
            if (strlen($query) > 1200 || preg_match('/[\x00-\x1f\x7f]/', $query)) {
                throw new InvalidArgumentException('The search link contains an invalid query.');
            }
            return ['type' => 'search', 'url' => self::ORIGIN.'/jobs/search'.($query !== '' ? '?'.$query : '')];
        }
        throw new InvalidArgumentException('Use a Work Hub job vacancy link or search results link.');
    }

    private function fetch(string $url): ?string
    {
        // URLs are normalized above or assembled from validated 24-hex IDs.
        $response = Http::withoutRedirecting()->connectTimeout(5)->timeout(8)
            ->withHeaders(['Accept' => 'text/html', 'User-Agent' => 'JWTC-AdminJobImport/1.0'])
            ->get($url);
        if ($response->status() !== 200 || strlen($response->body()) > 2000000) {
            return null;
        }
        return $response->body();
    }

    private function xpath(string $html): DOMXPath
    {
        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $doc->loadHTML($html, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        return new DOMXPath($doc);
    }

    private function text(DOMXPath $xpath, string $expression): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $xpath->evaluate($expression)) ?? '');
    }

    /** Discover search-card IDs; never follow arbitrary links from external HTML. */
    public function searchListingUrls(string $html): array
    {
        $xpath = $this->xpath($html);
        $links = [];
        foreach ($xpath->query('//*[@data-testid]') as $node) {
            if (!preg_match('/^searchResultCard-([0-9a-f]{24})$/i', $node->getAttribute('data-testid'), $match)) {
                continue;
            }
            $tags = strtolower(trim(preg_replace('/\s+/u', ' ', $xpath->evaluate(
                'string(.//*[@data-testid="searchResultsCardTags"])', $node
            )) ?? ''));
            // Avoid fetching known ineligible results, then re-check on each actual vacancy page.
            if ($tags !== '' && (!str_contains($tags, 'on-site')
                || !str_contains($tags, 'permanent') || !str_contains($tags, 'full time'))) {
                continue;
            }
            $link = self::ORIGIN.'/jobs/'.strtolower($match[1]);
            $links[$link] = $link;
            if (count($links) >= self::MAX_SEARCH_LISTINGS) break;
        }
        // Support sites which render real anchors rather than interactive cards.
        if (!$links) {
            foreach ($xpath->query('//a[@href]') as $node) {
                $href = trim($node->getAttribute('href'));
                if (preg_match('~^/jobs/([0-9a-f]{24})/?(?:\?.*)?$~i', $href, $match)) {
                    $link = self::ORIGIN.'/jobs/'.strtolower($match[1]);
                    $links[$link] = $link;
                }
                if (count($links) >= self::MAX_SEARCH_LISTINGS) break;
            }
        }
        return array_values($links);
    }

    /** Parse only explicit fields in the vacancy details table. Fail closed. */
    public function parseListing(string $html, string $url): ?array
    {
        $xpath = $this->xpath($html);
        $title = $this->text($xpath, 'string((//main//h1 | //h1)[1])');
        $fields = [];
        foreach ($xpath->query('//table//tr') as $row) {
            $label = rtrim(strtolower(trim($xpath->evaluate('string(./th[1])', $row))), ': ');
            $value = trim(preg_replace('/\s+/u', ' ', $xpath->evaluate('string(./td[1])', $row)) ?? '');
            if ($label !== '' && $value !== '') {
                $fields[$label] = $value;
            }
        }
        if ($title === '' || empty($fields['company']) || empty($fields['posting date'])
            || empty($fields['closing date'])
            || strtolower($fields['hours'] ?? '') !== 'full-time'
            || strtolower($fields['working pattern'] ?? '') !== 'on-site'
            || strtolower($fields['job type'] ?? '') !== 'permanent') {
            return null;
        }
        try {
            $posted = Carbon::createFromFormat('!j M Y', $fields['posting date']);
            $closing = Carbon::createFromFormat('!j M Y', $fields['closing date']);
            if (!$posted || !$closing
                || $posted->format('j M Y') !== $fields['posting date']
                || $closing->format('j M Y') !== $fields['closing date']) return null;
        } catch (\Throwable $error) {
            return null;
        }
        if ($posted->isAfter(today()) || $posted->isBefore(today()->subDays(14))
            || $closing->isBefore(today())) {
            return null;
        }
        $summary = $this->text($xpath, 'string((//main//h2[normalize-space(.)="Summary"]/following-sibling::p)[1])');
        return [
            'source' => 'uk_government_jobs',
            'source_url' => $url,
            'source_hash' => hash('sha256', $url),
            'title' => mb_substr($title, 0, 255),
            'company_name' => mb_substr($fields['company'], 0, 255),
            'location' => isset($fields['location']) ? mb_substr($fields['location'], 0, 255) : null,
            'salary' => isset($fields['salary']) ? mb_substr($fields['salary'], 0, 255) : null,
            'application_deadline' => $closing->toDateString(),
            'posted_on' => $posted->toDateString(),
            'workplace' => 'onsite',
            'employment_type' => 'permanent',
            'schedule' => 'full_time',
            'summary' => $summary !== '' ? mb_substr($summary, 0, 500) : null,
            'status' => 'draft',
        ];
    }

    public function import(string $sourceUrl, int $adminId): array
    {
        $source = $this->normalizeUrl($sourceUrl);
        $html = $this->fetch($source['url']);
        if ($html === null) {
            throw new InvalidArgumentException('Work Hub did not return a usable page. Please try again.');
        }
        $links = $source['type'] === 'job'
            ? [$source['url']]
            : $this->searchListingUrls($html);

        $added = 0;
        $skipped = 0;
        $checked = 0;
        $started = microtime(true);
        foreach ($links as $url) {
            if (microtime(true) - $started > 19) break;
            $checked++;
            try {
                $detailHtml = $source['type'] === 'job' ? $html : $this->fetch($url);
                $record = $detailHtml === null ? null : $this->parseListing($detailHtml, $url);
                if ($record === null) {
                    $skipped++;
                    continue;
                }
                $record['imported_by'] = $adminId;
                $record['created_at'] = now();
                $record['updated_at'] = now();
                $result = DB::table('external_job_imports')->insertOrIgnore($record);
                $result ? $added++ : $skipped++;
            } catch (\Throwable $error) {
                $skipped++;
            }
        }

        return ['added' => $added, 'skipped' => $skipped, 'checked' => $checked, 'found' => count($links)];
    }
}
