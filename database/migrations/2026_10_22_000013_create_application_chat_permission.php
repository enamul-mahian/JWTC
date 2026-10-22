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
                'name' => 'manage-application-chat',
                'guard_name' => 'web',
            ]);
        }
        // Deliberately do NOT grant access to any admin or staff role.
    }

    public function down(): void
    {
        // Retain audited permission grants unless an admin revokes them.
    }
};
