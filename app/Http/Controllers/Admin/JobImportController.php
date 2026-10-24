<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class JobImportController extends Controller
{
    private const HEADERS = ['title', 'company', 'location', 'employment_type', 'workplace', 'salary_min', 'salary_max', 'salary_currency', 'salary_period', 'summary', 'requirements', 'deadline', 'apply_url', 'source_url', 'source_id'];

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            in_array(strtolower((string) $request->user()?->user_type), ['admin', 'super_admin'], true)
                && $request->user()->can('active-job'),
            403
        );
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        $batches = DB::table('job_import_batches')->latest()->paginate(20);
        return view('admin.job.import', compact('batches'));
    }

    public function upload(Request $request)
    {
        $this->authorizeAdmin($request);
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:2048']]);
        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'rb');
        if (!$handle) throw ValidationException::withMessages(['file' => 'Could not read CSV file.']);

        try {
            $headers = fgetcsv($handle);
            if (!$headers) throw ValidationException::withMessages(['file' => 'CSV is empty.']);
            $headers = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $headers);
            if (count($headers) !== count(array_unique($headers)) || array_diff(['title', 'company', 'apply_url'], $headers)) {
                throw ValidationException::withMessages(['file' => 'CSV requires unique column names and title, company, apply_url columns.']);
            }
            if (array_diff($headers, self::HEADERS)) {
                throw ValidationException::withMessages(['file' => 'CSV contains unsupported columns.']);
            }

            $rows = [];
            $seen = [];
            while (($values = fgetcsv($handle)) !== false) {
                if (count($rows) >= 500) throw ValidationException::withMessages(['file' => 'Maximum 500 rows per upload.']);
                if (count($values) === 1 && trim((string)$values[0]) === '') continue;
                if (count($values) !== count($headers)) throw ValidationException::withMessages(['file' => 'CSV column count mismatch at row '.(count($rows) + 2)]);
                $data = array_combine($headers, array_map('trim', $values));
                $validator = Validator::make($data, [
                    'title' => 'required|string|max:255',
                    'company' => 'required|string|max:255',
                    'apply_url' => 'required|url|starts_with:https://|max:2048',
                    'source_url' => 'nullable|url|starts_with:https://|max:2048',
                    'source_id' => 'nullable|string|max:255',
                    'deadline' => 'nullable|date_format:Y-m-d',
                    'salary_min' => 'nullable|numeric|min:0',
                    'salary_max' => 'nullable|numeric|min:0',
                    'summary' => 'nullable|string|max:20000',
                    'requirements' => 'nullable|string|max:20000',
                ]);
                $key = hash('sha256', strtolower($data['source_id'] ?: ($data['source_url'] ?: $data['apply_url'])));
                $reason = $validator->fails() ? implode('; ', $validator->errors()->all()) : null;
                if (!$reason && !empty($data['deadline']) && $data['deadline'] < now()->toDateString()) $reason = 'Expired deadline';
                if (!$reason && isset($seen[$key])) $reason = 'Duplicate within CSV';
                if (!$reason && DB::table('job_import_rows')->where('source_key', $key)->exists()) $reason = 'Previously imported';
                $seen[$key] = true;
                $rows[] = ['source_key' => $key, 'status' => $reason ? 'skipped' : 'pending', 'reason' => $reason, 'payload' => json_encode($data, JSON_THROW_ON_ERROR)];
            }
        } finally {
            fclose($handle);
        }
        if (!$rows) throw ValidationException::withMessages(['file' => 'No job rows found.']);
        $id = DB::transaction(function () use ($request, $file, $rows) {
            $id = DB::table('job_import_batches')->insertGetId([
                'created_by' => $request->user()->id,
                'filename' => $file->getClientOriginalName(),
                'total_rows' => count($rows),
                'ready_rows' => count(array_filter($rows, fn ($r) => $r['status'] === 'pending')),
                'skipped_rows' => count(array_filter($rows, fn ($r) => $r['status'] === 'skipped')),
                'status' => 'staged', 'created_at' => now(), 'updated_at' => now()
            ]);
            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('job_import_rows')->insert(array_map(fn ($r) => $r + ['batch_id' => $id, 'created_at' => now(), 'updated_at' => now()], $chunk));
            }
            return $id;
        });
        return redirect()->route('admin.job-import.show', $id)->with('success', 'CSV staged for review. Nothing published.');
    }

    public function show(Request $request, int $batch)
    {
        $this->authorizeAdmin($request);
        $record = DB::table('job_import_batches')->find($batch);
        abort_unless($record, 404);
        $rows = DB::table('job_import_rows')->where('batch_id', $batch)->orderBy('id')->paginate(50);
        return view('admin.job.import-show', compact('record', 'rows'));
    }
}
