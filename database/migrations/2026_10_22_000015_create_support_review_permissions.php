<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) return;
        foreach (['manage-support-tickets', 'review-refund-requests',
            'confirm-refund-settlement'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        // No permission is auto-granted to any role or account.
    }

    public function down(): void
    {
        // Explicit security grants remain until independently revoked.
    }
};
