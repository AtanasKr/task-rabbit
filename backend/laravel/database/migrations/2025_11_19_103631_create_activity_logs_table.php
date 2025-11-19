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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnDelete();

            $table->string('action_type', 100);
            $table->string('field_name', 100)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

            $table->dateTime('created_at')->useCurrent();

            $table->index(['task_id', 'created_at'], 'idx_activity_logs_task');
            $table->index(['user_id', 'created_at'], 'idx_activity_logs_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
