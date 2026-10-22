<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\ApplicationStatusCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ApplicationStatusDefinitionController extends Controller
{
    private function authorizeEdit(Request $request): void
    {
        abort_unless($request->user()?->can('active-job'), 403);
        abort_unless(Schema::hasTable('application_status_definitions'), 503,
            'Apply the additive status migration before editing statuses.');
    }

    public function index(Request $request, ApplicationStatusCatalog $catalog)
    {
        $this->authorizeEdit($request);
        return view('admin.application_statuses.index', ['statuses' => $catalog->all()]);
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:180'],
            'progress' => ['required', 'integer', 'between:0,100'],
            'sort_order' => ['required', 'integer', 'between:1,65535'],
            'is_terminal' => ['required', Rule::in(['0', '1', 0, 1])],
            'is_active' => ['required', Rule::in(['0', '1', 0, 1])],
        ];
    }

    public function store(Request $request)
    {
        $this->authorizeEdit($request);
        $data = $request->validate($this->rules());
        $data['is_terminal'] = (bool) $data['is_terminal'];
        $data['is_active'] = (bool) $data['is_active'];

        DB::transaction(function () use ($request, $data) {
            $id = DB::table('application_status_definitions')->insertGetId(
                $data + ['created_at' => now(), 'updated_at' => now()]
            );
            DB::table('application_status_definition_events')->insert([
                'status_id' => $id,
                'actor_id' => $request->user()->id,
                'action' => 'create',
                'before' => null,
                'after' => json_encode(['id' => $id] + $data),
                'created_at' => now(),
            ]);
        });
        return redirect()->route('admin.application-statuses.index')
            ->withSuccess('New workflow stage created.');
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeEdit($request);
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($request, $id, $data) {
            $old = DB::table('application_status_definitions')
                ->where('id', $id)->lockForUpdate()->first();
            abort_unless($old, 404);

            // Never deactivate a status that is still assigned to any application.
            if (!(bool) $data['is_active'] &&
                Application::where('status', $id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'is_active' => 'This stage is used by existing applications and cannot be disabled.',
                ]);
            }

            $changes = [
                'name' => $data['name'],
                'progress' => (int) $data['progress'],
                'sort_order' => (int) $data['sort_order'],
                'is_terminal' => (bool) $data['is_terminal'],
                'is_active' => (bool) $data['is_active'],
            ];
            $before = [
                'name' => $old->name, 'progress' => (int) $old->progress,
                'sort_order' => (int) $old->sort_order,
                'is_terminal' => (bool) $old->is_terminal,
                'is_active' => (bool) $old->is_active,
            ];

            if ($before === $changes) {
                return;
            }

            DB::table('application_status_definitions')
                ->where('id', $id)->update($changes + ['updated_at' => now()]);

            DB::table('application_status_definition_events')->insert([
                'status_id' => $id,
                'actor_id' => $request->user()->id,
                'action' => 'update',
                'before' => json_encode($before),
                'after' => json_encode($changes),
                'created_at' => now(),
            ]);
        });

        return redirect()->route('admin.application-statuses.index')
            ->withSuccess('Workflow stage updated.');
    }
}
