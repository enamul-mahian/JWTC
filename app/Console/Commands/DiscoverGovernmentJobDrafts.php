<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DiscoverGovernmentJobDrafts extends Command
{
    protected $signature = 'jobs:discover-government {--pages=3 : Maximum search pages, 1-5}';
    protected $description = 'Discover eligible UK government Work Hub listings as unpublished drafts (no automatic publishing)';

    public function handle(): int
    {
        $pages = (int) $this->option('pages');
        if ($pages < 1 || $pages > 5) {
            $this->error('Pages must be between 1 and 5.');
            return self::FAILURE;
        }
        $importerId = (int) config('services.government_jobs.import_user_id');
        if ($importerId < 1 || !DB::table('users')->where('id', $importerId)->exists()) {
            $this->error('Set GOVERNMENT_JOBS_IMPORT_USER_ID to an existing admin user ID before running.');
            return self::FAILURE;
        }
        $host = 'www.jobs.service.gov.uk';
        $discovered = [];
        for ($page = 1; $page <= $pages; $page++) {
            try {
                $response = Http::timeout(15)->retry(2, 500)->get('https://'.$host.'/jobs/search', [
                    'pageNumber' => $page,
                ]);
                if (!$response->successful() || strlen($response->body()) > 2500000) throw new \RuntimeException('Search source unavailable');
                $document = new \DOMDocument();
                $prior = libxml_use_internal_errors(true);
                $document->loadHTML($response->body(), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
                libxml_clear_errors();
                libxml_use_internal_errors($prior);
                $xpath = new \DOMXPath($document);
                foreach ($xpath->query('//a[@href]') as $link) {
                    $href = trim($link->getAttribute('href'));
                    $url = str_starts_with($href, '/') ? 'https://'.$host.$href : $href;
                    $parsedHost = strtolower((string) parse_url($url, PHP_URL_HOST));
                    $path = (string) parse_url($url, PHP_URL_PATH);
                    if (in_array($parsedHost, [$host, 'jobs.service.gov.uk'], true)
                        && preg_match('~^/jobs/[^/]+/?$~', $path)) {
                        $discovered[explode('?', $url)[0]] = true;
                    }
                }
            } catch (\Throwable $error) {
                $this->warn('A search page could not be checked; remaining pages continue.');
            }
        }

        $added = 0;
        foreach (array_slice(array_keys($discovered), 0, 80) as $url) {
            try {
                $response = Http::timeout(12)->get($url);
                if (!$response->successful() || strlen($response->body()) > 1500000) continue;
                $doc = new \DOMDocument();
                $prior = libxml_use_internal_errors(true);
                $doc->loadHTML($response->body(), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
                libxml_clear_errors();
                libxml_use_internal_errors($prior);
                $xpath = new \DOMXPath($doc);
                // Work Hub exposes the actual values as an HTML definition table on many listings.
                // Do not infer missing details: only stage when every eligibility label is explicit.
                $heading = trim((string) $xpath->evaluate('string(//h1[1])'));
                $pageText = preg_replace('/\\s+/u', ' ', trim((string) $xpath->evaluate('string(//body)')));
                $field = static function (string $label) use ($pageText): ?string {
                    if (!preg_match('/'.preg_quote($label, '/').':\\s*(.*?)\\s*(?=Company:|Salary:|Hours:|Location:|Working pattern:|Job type:|Posting date:|Closing date:|Apply for this job|Summary|$)/iu', $pageText, $match)) {
                        return null;
                    }
                    return trim($match[1]);
                };
                $hours = $field('Hours');
                $working = $field('Working pattern');
                $jobType = $field('Job type');
                $postedText = $field('Posting date');
                $deadlineText = $field('Closing date');
                if ($heading !== '' && $field('Company') && $postedText
                    && mb_strtolower((string) $hours) === 'full-time'
                    && mb_strtolower((string) $working) === 'on-site'
                    && mb_strtolower((string) $jobType) === 'permanent') {
                    try {
                        $posted = Carbon::createFromFormat('!j M Y', $postedText);
                        $deadline = $deadlineText ? Carbon::createFromFormat('!j M Y', $deadlineText) : null;
                        if ($posted && $posted->format('j M Y') === $postedText
                            && !$posted->isFuture() && $posted->gte(now()->startOfDay()->subDays(14))
                            && $deadline && $deadline->format('j M Y') === $deadlineText
                            && !$deadline->lt(now()->startOfDay())) {
                            $added += DB::table('external_job_imports')->insertOrIgnore([
                                'source' => 'uk_government_jobs', 'source_url' => $url,
                                'source_hash' => hash('sha256', $url),
                                'title' => mb_substr($heading, 0, 255),
                                'company_name' => mb_substr((string) $field('Company'), 0, 255),
                                'location' => mb_substr((string) ($field('Location') ?? ''), 0, 255) ?: null,
                                'salary' => mb_substr((string) ($field('Salary') ?? ''), 0, 255) ?: null,
                                'application_deadline' => $deadline->toDateString(),
                                'posted_on' => $posted->toDateString(),
                                'workplace' => 'onsite', 'employment_type' => 'permanent',
                                'schedule' => 'full_time', 'summary' => null,
                                'status' => 'draft', 'imported_by' => $importerId,
                                'created_at' => now(), 'updated_at' => now(),
                            ]);
                        }
                    } catch (\Throwable $invalidDate) {
                        // Fail closed when source date formats unexpectedly change.
                    }
                }
                foreach ($xpath->query('//script[@type="application/ld+json"]') as $node) {
                    $data = json_decode($node->textContent, true);
                    if (!is_array($data)) continue;
                    $candidates = array_is_list($data) ? $data : (isset($data['@graph']) ? $data['@graph'] : [$data]);
                    foreach ($candidates as $job) {
                        if (!is_array($job) || !str_contains(strtolower((string)($job['@type'] ?? '')), 'jobposting')) continue;
                        $type = strtolower((string) ($job['employmentType'] ?? ''));
                        $description = strip_tags((string) ($job['description'] ?? ''));
                        $posted = substr((string) ($job['datePosted'] ?? ''), 0, 10);
                        $deadline = substr((string) ($job['validThrough'] ?? ''), 0, 10);
                        $locationType = strtolower((string) ($job['jobLocationType'] ?? ''));
                        if (!str_contains($type, 'full') || !str_contains($type, 'permanent')
                            || str_contains($locationType, 'telecommute')
                            || !$posted || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $posted)) continue;
                        $date = Carbon::createFromFormat('!Y-m-d', $posted);
                        if (!$date || $date->isFuture() || $date->lt(now()->startOfDay()->subDays(14))) continue;
                        if ($deadline && preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline) && $deadline < now()->toDateString()) continue;
                        $employer = $job['hiringOrganization']['name'] ?? null;
                        $place = $job['jobLocation']['address']['addressLocality'] ?? null;
                        $title = trim((string) ($job['title'] ?? ''));
                        if (!$title || !$employer) continue;
                        $added += DB::table('external_job_imports')->insertOrIgnore([
                            'source' => 'uk_government_jobs', 'source_url' => $url,
                            'source_hash' => hash('sha256', $url),
                            'title' => mb_substr($title, 0, 255),
                            'company_name' => mb_substr((string) $employer, 0, 255),
                            'location' => $place ? mb_substr((string) $place, 0, 255) : null,
                            'salary' => null, 'application_deadline' => $deadline ?: null,
                            'posted_on' => $posted, 'workplace' => 'onsite',
                            'employment_type' => 'permanent', 'schedule' => 'full_time',
                            'summary' => mb_substr($description, 0, 5000), 'status' => 'draft',
                            'imported_by' => $importerId,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                }
            } catch (\Throwable $error) {
                $this->warn('One listing could not be imported.');
            }
        }
        $this->info("Discovered ".count($discovered)." links; added {$added} unpublished drafts.");
        return self::SUCCESS;
    }
}
