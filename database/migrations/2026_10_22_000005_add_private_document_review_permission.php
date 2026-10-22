<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration {
    public function up(): void
    {
        // Permission is created without granting it automatically to any role.
        // An authorized system owner can grant it explicitly through RBAC.
        if (Schema::hasTable('permissions')) {
            Permission::firstOrCreate([
                'name' => 'review-private-documents',
                'guard_name' => 'web',
            ]);
        }
    }

    public function down(): void
    {
        // Do not delete an RBAC permission (and its grants) automatically on
        // rollback; removing authorization requires a separate audit.
    }
};
