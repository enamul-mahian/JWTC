<?php

namespace Tests\Unit;

use App\Services\WorkHubLinkImporter;
use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class WorkHubLinkImporterTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function html(string $working = 'On-site', string $posted = '19 Oct 2026', string $deadline = '18 Nov 2026'): string
    {
        return '<main><h1>Transport Operations Administrator</h1><table><tbody>'
            .'<tr><th>Company:</th><td>Talent Finder</td></tr>'
            .'<tr><th>Salary:</th><td>£30,000 a year</td></tr>'
            .'<tr><th>Hours:</th><td>Full-time</td></tr>'
            .'<tr><th>Location:</th><td>London</td></tr>'
            .'<tr><th>Working pattern:</th><td>'.$working.'</td></tr>'
            .'<tr><th>Job type:</th><td>Permanent</td></tr>'
            .'<tr><th>Posting date:</th><td>'.$posted.'</td></tr>'
            .'<tr><th>Closing date:</th><td>'.$deadline.'</td></tr>'
            .'</tbody></table><h2>Summary</h2><p>A short original job summary.</p></main>';
    }

    public function test_normalizes_work_hub_search_and_vacancy_urls(): void
    {
        $service = new WorkHubLinkImporter();
        $this->assertSame(
            ['type' => 'search', 'url' => 'https://www.jobs.service.gov.uk/jobs/search?pageNumber=1'],
            $service->normalizeUrl('https://jobs.service.gov.uk/jobs/search?pageNumber=1')
        );
        $this->assertSame(
            ['type' => 'job', 'url' => 'https://www.jobs.service.gov.uk/jobs/6ad61edeb0af0cb429b49a30'],
            $service->normalizeUrl('https://www.jobs.service.gov.uk/jobs/6ad61edeb0af0cb429b49a30')
        );
    }

    public function test_rejects_arbitrary_hosts_protocols_ports_credentials_and_paths(): void
    {
        $service = new WorkHubLinkImporter();
        foreach ([
            'http://www.jobs.service.gov.uk/jobs/search',
            'https://www.jobs.service.gov.uk.evil.example/jobs/search',
            'https://localhost/jobs/search',
            'https://user@www.jobs.service.gov.uk/jobs/search',
            'https://www.jobs.service.gov.uk:8888/jobs/search',
            'https://www.jobs.service.gov.uk/jobs/6ad61edeb0af0cb429b49a30/apply',
            'https://www.jobs.service.gov.uk/jobs/search#hidden',
        ] as $link) {
            try {
                $service->normalizeUrl($link);
                $this->fail('Unsafe URL was accepted: '.$link);
            } catch (InvalidArgumentException $e) {
                $this->assertNotSame('', $e->getMessage());
            }
        }
    }

    public function test_parses_a_live_work_hub_style_table_as_unpublished_draft(): void
    {
        Carbon::setTestNow('2026-10-25 10:00:00');
        $service = new WorkHubLinkImporter();
        $url = 'https://www.jobs.service.gov.uk/jobs/6ad61edeb0af0cb429b49a30';
        $job = $service->parseListing($this->html(), $url);
        $this->assertNotNull($job);
        $this->assertSame('Transport Operations Administrator', $job['title']);
        $this->assertSame('Talent Finder', $job['company_name']);
        $this->assertSame('2026-10-19', $job['posted_on']);
        $this->assertSame('2026-11-18', $job['application_deadline']);
        $this->assertSame('onsite', $job['workplace']);
        $this->assertSame('draft', $job['status']);
        $this->assertSame($url, $job['source_url']);
    }

    public function test_rejects_hybrid_expired_old_and_missing_close_dates(): void
    {
        Carbon::setTestNow('2026-10-25 10:00:00');
        $service = new WorkHubLinkImporter();
        foreach ([
            $this->html('Hybrid'),
            $this->html('On-site', '1 Oct 2026'),
            $this->html('On-site', '19 Oct 2026', '20 Oct 2026'),
            $this->html('On-site', '19 Oct 2026', ''),
        ] as $html) {
            $this->assertNull($service->parseListing($html, 'https://www.jobs.service.gov.uk/jobs/6ad61edeb0af0cb429b49a30'));
        }
    }

    public function test_extracts_search_cards_and_filters_known_ineligible_results(): void
    {
        $html = '<main>'
            .'<div data-testid="searchResultCard-6ad61edeb0af0cb429b49a30">'
            .'<p data-testid="searchResultsCardTags">On-sitePermanentFull time</p></div>'
            .'<div data-testid="searchResultCard-6ad8daa5827b1f1f951f4d63">'
            .'<p data-testid="searchResultsCardTags">HybridPermanentFull time</p></div>'
            .'</main>';
        $this->assertSame(
            ['https://www.jobs.service.gov.uk/jobs/6ad61edeb0af0cb429b49a30'],
            (new WorkHubLinkImporter())->searchListingUrls($html)
        );
    }
    public function test_pasted_work_hub_text_is_validated_and_parsed_into_a_draft(): void
    {
        Carbon::setTestNow('2026-10-25 10:00:00');
        $pasted = "Multi Skilled Kitchen Fitter\n".
            "Company: | Reading Borough Council\n".
            "Salary: £32,293 to £38,262 a year\n".
            "Hours: Full-time\n".
            "Location: Reading, RG2 0QX\n".
            "Working pattern: On-site\n".
            "Job type: Permanent\n".
            "Posting date: 15 Oct 2026\n".
            "Closing date: 25 Oct 2026\n".
            "Summary\n".
            "Installing and refurbishing kitchens for council homes.\n";
        $record = (new WorkHubLinkImporter())->parsePastedListing(
            $pasted, 'https://www.jobs.service.gov.uk/jobs/6ad095af813f93ba4140e36e'
        );
        $this->assertNotNull($record);
        $this->assertSame('Multi Skilled Kitchen Fitter', $record['title']);
        $this->assertSame('Reading Borough Council', $record['company_name']);
        $this->assertSame('2026-10-25', $record['application_deadline']);
        $this->assertSame('draft', $record['status']);
        $this->assertSame('https://www.jobs.service.gov.uk/jobs/6ad095af813f93ba4140e36e', $record['source_url']);
        $this->assertNull((new WorkHubLinkImporter())->parsePastedListing(
            str_replace('On-site', 'Hybrid', $pasted),
            'https://www.jobs.service.gov.uk/jobs/6ad095af813f93ba4140e36e'
        ));
        $this->assertNull((new WorkHubLinkImporter())->parsePastedListing(
            $pasted, 'https://www.jobs.service.gov.uk/jobs/search?pageNumber=1'
        ));
    }


}
