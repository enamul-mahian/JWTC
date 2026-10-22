<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('application_status_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(100);
            $table->boolean('is_terminal')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        $defaults = [
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

        DB::table('application_status_definitions')->insert(
            array_map(static fn ($row) => $row + [
                'created_at' => $now, 'updated_at' => $now,
            ], $defaults)
        );

        Schema::create('application_status_events', function (Blueprint $table) {
            $table->id();
            // No cascade delete: preserve the historical audit if an application
            // is later soft-deleted or otherwise removed.
            $table->unsignedBigInteger('application_id')->index();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->unsignedBigInteger('from_status_id')->nullable();
            $table->unsignedBigInteger('to_status_id');
            $table->string('status_name', 180);
            $table->unsignedTinyInteger('progress');
            $table->string('source', 24)->default('admin');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['application_id', 'created_at']);
        });

        Schema::create('application_status_definition_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('status_id')->index();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 30);
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_definition_events');
        Schema::dropIfExists('application_status_events');
        Schema::dropIfExists('application_status_definitions');
    }
};
