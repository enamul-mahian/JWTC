<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ApplicationStatusManager
{
    /**
     * Lock the application before changing status, then log an immutable
     * snapshot in the same transaction. No event is written on no-op changes.
     */
    public function transition(
        Application $application,
        int $newStatus,
        ?User $actor,
        string $source,
        ?string $note = null
    ): Application {
        $catalog = app(ApplicationStatusCatalog::class);
        $definition = $catalog->find($newStatus);

        if (!$definition || !$definition['is_active']) {
            throw ValidationException::withMessages(['status' => 'Select an active application status.']);
        }
        if (!in_array($source, ['admin', 'employer', 'system'], true)) {
            throw new \InvalidArgumentException('Invalid status update source.');
        }
        if (!Schema::hasTable('application_status_events')) {
            throw new \RuntimeException('Status history migration must be applied before changing application statuses.');
        }

        return DB::transaction(function () use ($application, $newStatus, $actor, $source, $note, $definition) {
            $locked = Application::whereKey($application->getKey())->lockForUpdate()->firstOrFail();
            $before = $locked->status === null ? null : (int) $locked->status;
            if ($before === $newStatus) {
                return $locked;
            }

            $locked->status = $newStatus;
            $locked->save();

            DB::table('application_status_events')->insert([
                'application_id' => $locked->getKey(),
                'actor_id' => $actor?->getKey(),
                'from_status_id' => $before,
                'to_status_id' => $newStatus,
                'status_name' => $definition['name'],
                'progress' => $definition['progress'],
                'source' => $source,
                'note' => $note,
                'created_at' => now(),
            ]);

            return $locked;
        }, 3);
    }
}
