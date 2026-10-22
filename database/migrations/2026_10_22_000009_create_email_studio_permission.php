<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permissions')) {
            Permission::firstOrCreate([
                'name' => 'manage-email-studio',
                'guard_name' => 'web',
            ]);
        }
        // Deliberately do not grant this sensitive send permission to any role.
    }

    public function down(): void
    {
        // Preserve assigned permission grants unless deliberately revoked.
    }
};
