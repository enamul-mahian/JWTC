<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dashboard\DegreeLavel;
use App\Models\Dashboard\DegreeName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobDegreeOptionsController extends Controller
{
    private function authorizeEditor(Request $request): void
    {
        abort_unless(
            in_array(strtolower((string) $request->user()?->user_type), ['admin', 'super_admin'], true)
            && $request->user()->can('active-job'),
            403
        );
    }

    private function duplicateName(string $table, string $name, ?int $excludeId = null, ?int $levelId = null): bool
    {
        $query = DB::table($table)->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($name))]);
        if ($excludeId !== null) $query->where('id', '!=', $excludeId);
        if ($levelId !== null) $query->where('degree_lavel_id', $levelId);
        return $query->exists();
    }

    private function activeLevel(int $levelId): bool
    {
        return DegreeLavel::whereKey($levelId)
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', 1))
            ->exists();
    }

    public function index(Request $request)
    {
        $this->authorizeEditor($request);

        return response()->view('admin.job.degrees', [
            'setting' => gs(),
            'levels' => DegreeLavel::orderBy('name')->orderBy('id')->get(['id', 'name', 'status']),
            'degrees' => DegreeName::with('degreeLavel:id,name,status')
                ->orderBy('name')->orderBy('id')->get(['id', 'name', 'degree_lavel_id', 'status']),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function addLevel(Request $request)
    {
        $this->authorizeEditor($request);
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $name = trim($data['name']);
        if ($this->duplicateName('degree_lavels', $name)) {
            throw ValidationException::withMessages(['name' => 'An education level with this name already exists. Edit or enable the existing entry.']);
        }
        DegreeLavel::create(['name' => $name, 'status' => 1]);
        return redirect()->route('admin.job-degrees.index')->with('success', 'Education level added.');
    }

    public function updateLevel(Request $request, int $id)
    {
        $this->authorizeEditor($request);
        $level = DegreeLavel::findOrFail($id);
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $name = trim($data['name']);
        if ($this->duplicateName('degree_lavels', $name, $id)) {
            throw ValidationException::withMessages(['name' => 'Another education level already has this name.']);
        }
        $level->update(['name' => $name]);
        return redirect()->route('admin.job-degrees.index')->with('success', 'Education level renamed. Historical records retain the same ID.');
    }

    public function toggleLevel(Request $request, int $id)
    {
        $this->authorizeEditor($request);
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $level = DegreeLavel::findOrFail($id);
        $level->update(['status' => $data['enabled'] ? 1 : 0]);
        return redirect()->route('admin.job-degrees.index')->with('success',
            $data['enabled'] ? 'Education level enabled.' : 'Education level hidden from new job choices; existing records preserved.');
    }

    public function addDegree(Request $request)
    {
        $this->authorizeEditor($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'degree_lavel_id' => ['required', 'integer', 'exists:degree_lavels,id'],
        ]);
        $levelId = (int) $data['degree_lavel_id'];
        if (!$this->activeLevel($levelId)) {
            throw ValidationException::withMessages(['degree_lavel_id' => 'Select an enabled education level.']);
        }
        $name = trim($data['name']);
        if ($this->duplicateName('degree_names', $name, null, $levelId)) {
            throw ValidationException::withMessages(['name' => 'This degree already exists under the selected level. Edit or enable it instead.']);
        }
        DegreeName::create(['name' => $name, 'degree_lavel_id' => $levelId, 'status' => 1]);
        return redirect()->route('admin.job-degrees.index')->with('success', 'Degree added. Reload the job form to choose it.');
    }

    public function updateDegree(Request $request, int $id)
    {
        $this->authorizeEditor($request);
        $degree = DegreeName::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'degree_lavel_id' => ['required', 'integer', 'exists:degree_lavels,id'],
        ]);
        $levelId = (int) $data['degree_lavel_id'];
        if (!$this->activeLevel($levelId)) {
            throw ValidationException::withMessages(['degree_lavel_id' => 'Select an enabled education level.']);
        }
        $name = trim($data['name']);
        if ($this->duplicateName('degree_names', $name, $id, $levelId)) {
            throw ValidationException::withMessages(['name' => 'This degree already exists under the selected level.']);
        }
        $degree->update(['name' => $name, 'degree_lavel_id' => $levelId]);
        return redirect()->route('admin.job-degrees.index')->with('success',
            'Degree updated. Its ID remains unchanged, so historical jobs and education records are preserved.');
    }

    public function toggleDegree(Request $request, int $id)
    {
        $this->authorizeEditor($request);
        $data = $request->validate(['enabled' => ['required', 'boolean']]);
        $degree = DegreeName::findOrFail($id);
        $degree->update(['status' => $data['enabled'] ? 1 : 0]);
        return redirect()->route('admin.job-degrees.index')->with('success',
            $data['enabled'] ? 'Degree enabled.' : 'Degree hidden from new job choices; historical references preserved.');
    }
}
