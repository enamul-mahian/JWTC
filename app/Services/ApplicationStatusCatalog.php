<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApplicationStatusCatalog
{
    /**
     * Mirrors historical status IDs and percentages. Fallback works before the
     * additive migration, so earlier applications are never renumbered.
     */
    public const LEGACY = [
        ['id' => 1, 'name' => 'File Under Process of Decision Making', 'progress' => 0, 'sort_order' => 10, 'is_terminal' => false, 'is_active' => true],
        ['id' => 2, 'name' => 'File Under Process of Assessment', 'progress' => 20, 'sort_order' => 20, 'is_terminal' => false, 'is_active' => true],
        ['id' => 3, 'name' => 'File Under Process of Acceptance', 'progress' => 30, 'sort_order' => 30, 'is_terminal' => false, 'is_active' => true],
        ['id' => 4, 'name' => 'File Under Process of Approval', 'progress' => 40, 'sort_order' => 40, 'is_terminal' => false, 'is_active' => true],
        ['id' => 5, 'name' => 'Agreement on Board', 'progress' => 50, 'sort_order' => 50, 'is_terminal' => false, 'is_active' => true],
        ['id' => 6, 'name' => 'Police/Criminal Clearance Certificate Received', 'progress' => 55, 'sort_order' => 60, 'is_terminal' => false, 'is_active' => true],
        ['id' => 7, 'name' => 'Medical Test Report Received', 'progress' => 60, 'sort_order' => 70, 'is_terminal' => false, 'is_active' => true],
        ['id' => 8, 'name' => 'COS Letter Processed', 'progress' => 70, 'sort_order' => 80, 'is_terminal' => false, 'is_active' => true],
        ['id' => 9, 'name' => 'VFS Appointment Date Approved', 'progress' => 75, 'sort_order' => 90, 'is_terminal' => false, 'is_active' => true],
        ['id' => 10, 'name' => 'Visa Processing Complete', 'progress' => 100, 'sort_order' => 100, 'is_terminal' => true, 'is_active' => true],
        ['id' => 11, 'name' => 'Rejected / Terminated', 'progress' => 0, 'sort_order' => 110, 'is_terminal' => true, 'is_active' => true],
    ];

    public function all(): array
    {
        if (!Schema::hasTable('application_status_definitions')) {
            return self::LEGACY;
        }
        $rows = DB::table('application_status_definitions')
            ->orderBy('sort_order')->orderBy('id')->get();
        if ($rows->isEmpty()) {
            return self::LEGACY;
        }
        return $rows->map(static fn ($row) => [
            'id' => (int) $row->id,
            'name' => (string) $row->name,
            'progress' => (int) $row->progress,
            'sort_order' => (int) $row->sort_order,
            'is_terminal' => (bool) $row->is_terminal,
            'is_active' => (bool) $row->is_active,
        ])->all();
    }

    public function find(?int $id): ?array
    {
        foreach ($this->all() as $status) {
            if ($status['id'] === $id) {
                return $status;
            }
        }
        return null;
    }

    public function active(): array
    {
        return array_values(array_filter($this->all(), static fn ($entry) => $entry['is_active']));
    }

    public function editable(): bool
    {
        return Schema::hasTable('application_status_definitions');
    }
}
