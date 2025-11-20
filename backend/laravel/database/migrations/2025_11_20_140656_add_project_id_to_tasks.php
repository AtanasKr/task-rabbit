<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('project_id')
                ->after('id')
                ->constrained('projects')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->index('project_id', 'idx_tasks_project');
            $table->index(['project_id', 'status_id'], 'idx_tasks_project_status');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropIndex('idx_tasks_project');
            $table->dropIndex('idx_tasks_project_status');
            $table->dropColumn('project_id');
        });
    }
};
