<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Services\WorkHubLinkImporter;
use InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ExternalJobImportController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless(in_array(strtolower((string) $request->user()?->user_type), ['admin', 'super_admin'], true)
            && $request->user()->can('active-job'), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        $jobs = DB::table('external_job_imports')->orderByDesc('id')->paginate(25);
        return response()->view('admin.job.external-import', compact('jobs'))->header('Cache-Control', 'private, no-store');
    }

    public function importLink(Request $request, WorkHubLinkImporter $importer)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['source_url' => ['required', 'string', 'max:2048']]);
        try {
            $result = $importer->import($data['source_url'], (int) $request->user()->id);
        } catch (InvalidArgumentException $error) {
            return back()->withInput()->withErrors(['source_url' => $error->getMessage()]);
        } catch (\Throwable $error) {
            report($error);
            return back()->withInput()->withErrors(['source_url' => 'Import could not complete. Try again later.']);
        }

        return redirect()->route('admin.external-jobs.index')->with('import_result',
            "Checked {$result['checked']} vacancies, saved {$result['added']} new drafts; skipped {$result['skipped']}. Nothing published.");
    }

    public function upload(Request $request)
    {
        $this->authorizeAdmin($request);
        $request->validate(['csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $handle = fopen($request->file('csv')->getRealPath(), 'rb');
        if (!$handle) throw ValidationException::withMessages(['csv' => 'Cannot open CSV.']);
        $header = fgetcsv($handle);
        $expected = ['source_url', 'title', 'company_name', 'location', 'salary',
            'application_deadline', 'posted_on', 'workplace', 'employment_type', 'schedule', 'summary'];
        $header = array_map(static fn ($x) => trim((string) $x), $header ?: []);
        if ($header !== $expected) {
            fclose($handle);
            throw ValidationException::withMessages(['csv' => 'CSV columns must match the documented template, in order.']);
        }
        $inserted = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if ($rowNumber > 1001) { $errors[] = 'Maximum 1000 rows per upload.'; break; }
            if (count($row) !== count($expected)) { $errors[] = "Row {$rowNumber}: incorrect number of columns"; continue; }
            $item = array_combine($expected, $row);
            $item = array_map(static fn ($v) => trim((string) $v), $item);
            $v = Validator::make($item, [
                'source_url' => ['required', 'url', 'max:2048', 'regex:/^https:\/\//i'],
                'title' => ['required', 'string', 'max:255'],
                'company_name' => ['required', 'string', 'max:255'],
                'location' => ['nullable', 'string', 'max:255'],
                'salary' => ['nullable', 'string', 'max:255'],
                'application_deadline' => ['nullable', 'date_format:Y-m-d'],
                'posted_on' => ['required', 'date_format:Y-m-d'],
                'workplace' => ['required', 'in:onsite'],
                'employment_type' => ['required', 'in:permanent'],
                'schedule' => ['required', 'in:full_time'],
                'summary' => ['nullable', 'string', 'max:5000'],
            ]);
            if ($v->fails()) { $errors[] = "Row {$rowNumber}: ".$v->errors()->first(); continue; }
            $host = strtolower((string) parse_url($item['source_url'], PHP_URL_HOST));
            if (!in_array($host, ['jobs.service.gov.uk', 'www.jobs.service.gov.uk'], true)) {
                $errors[] = "Row {$rowNumber}: source host is not jobs.service.gov.uk"; continue;
            }
            $posted = Carbon::createFromFormat('!Y-m-d', $item['posted_on']);
            if ($posted->isFuture() || $posted->lt(now()->startOfDay()->subDays(14))) {
                $skipped++; continue;
            }
            if ($item['application_deadline'] && $item['application_deadline'] < now()->toDateString()) {
                $skipped++; continue;
            }
            $url = $item['source_url'];
            $hash = hash('sha256', $url);
            $created = DB::table('external_job_imports')->insertOrIgnore([
                'source' => 'uk_government_jobs', 'source_url' => $url,
                'source_hash' => $hash, 'title' => $item['title'],
                'company_name' => $item['company_name'], 'location' => $item['location'] ?: null,
                'salary' => $item['salary'] ?: null,
                'application_deadline' => $item['application_deadline'] ?: null,
                'posted_on' => $item['posted_on'], 'workplace' => 'onsite',
                'employment_type' => 'permanent', 'schedule' => 'full_time',
                'summary' => $item['summary'] ?: null, 'status' => 'draft',
                'imported_by' => $request->user()->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $created ? $inserted++ : $skipped++;
        }
        fclose($handle);
        return redirect()->route('admin.external-jobs.index')
            ->with('import_result', "Imported {$inserted} drafts; skipped {$skipped}.")
            ->with('import_errors', array_slice($errors, 0, 30));
    }
    public function review(Request $request, int $id)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['decision' => ['required', 'in:approved,rejected']]);
        $updated = DB::table('external_job_imports')
            ->where('id', $id)->where('status', 'draft')
            ->where('posted_on', '>=', now()->startOfDay()->subDays(14)->toDateString())
            ->where(function ($query) {
                $query->whereNull('application_deadline')
                    ->orWhere('application_deadline', '>=', now()->toDateString());
            })
            ->update(['status' => $data['decision'], 'updated_at' => now()]);
        if (!$updated) {
            return redirect()->route('admin.external-jobs.index')
                ->withErrors(['review' => 'Draft not found, already reviewed, or no longer eligible.']);
        }
        return redirect()->route('admin.external-jobs.index')
            ->with('import_result', 'Import draft '. $id .' marked '. $data['decision'] .'. Not published.');
    }

    public function publication(Request $request, int $id)
    {
        $this->authorizeAdmin($request);
        $action = $request->validate([
            'action' => ['required', 'in:publish,unpublish'],
            'publishing_rights' => ['required_if:action,publish', 'accepted'],
        ])['action'];
        $result = DB::transaction(function () use ($id, $action, $request) {
            $record = DB::table('external_job_imports')->where('id', $id)->lockForUpdate()->first();
            if (!$record || !in_array($record->status, $action === 'publish' ? ['approved'] : ['published'], true)) {
                return false;
            }
            if ($action === 'publish') {
                $parsed = parse_url($record->source_url);
                if (!is_array($parsed) || strtolower((string) ($parsed['scheme'] ?? '')) !== 'https'
                    || !in_array(strtolower((string) ($parsed['host'] ?? '')), [
                        'jobs.service.gov.uk', 'www.jobs.service.gov.uk'
                    ], true)
                    || $record->source !== 'uk_government_jobs'
                    || !$record->application_deadline || !$record->posted_on
                    || $record->application_deadline < today()->toDateString()
                    || $record->posted_on < today()->subDays(14)->toDateString()
                    || $record->posted_on > today()->toDateString()
                    || $record->workplace !== 'onsite' || $record->employment_type !== 'permanent'
                    || $record->schedule !== 'full_time') {
                    return false;
                }
            }
            $updated = DB::table('external_job_imports')
                ->where('id', $id)->where('status', $record->status)
                ->update(['status' => $action === 'publish' ? 'published' : 'approved',
                    'updated_at' => now()]);
            if ($updated) {
                \Illuminate\Support\Facades\Log::info('External job publication status changed', [
                    'external_import_id' => $id, 'actor_id' => $request->user()->id, 'action' => $action
                ]);
            }
            return $updated > 0;
        });
        if (!$result) {
            return redirect()->route('admin.external-jobs.index')
                ->withErrors(['publication' => 'Job is not in the required review state or its source/eligibility is invalid.']);
        }
        return redirect()->route('admin.external-jobs.index')
            ->with('import_result', 'External job #'.$id.' publication action completed: '.$action.'. '.(
                $action === 'publish' ? 'Public link: '.route('government-jobs.show', $id)
                    : 'Removed from public listings.'
            ));
    }

}
