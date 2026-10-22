<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        // Create the permission but do not automatically grant it to any role.
        // Only approved administrators should gain cross-employer scheduling power.
        if (Schema::hasTable('permissions')) {
            Permission::firstOrCreate([
                'name' => 'manage-interviews',
                'guard_name' => 'web',
            ]);
        }
    }

    public function down(): void
    {
        // Intentional: preserve any audited grants until explicitly revoked.
    }
};
