<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->unsignedTinyInteger('status_id');
            $table->foreignId('assigned_to_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnDelete();
            $table->foreignId('created_by_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->date('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index('assigned_to_id', 'idx_tasks_assigned_to');
            $table->index('status_id', 'idx_tasks_status');
            $table->index('due_date', 'idx_tasks_due_date');
            $table->index(
                ['assigned_to_id', 'status_id'],
                'idx_tasks_assigned_status'
            );

            $table->foreign('status_id', 'fk_tasks_status')
                ->references('id')
                ->on('task_statuses')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('fk_tasks_status');
            $table->dropForeign(['assigned_to_id']);
            $table->dropForeign(['created_by_id']);
        });

        Schema::dropIfExists('tasks');
    }
};
