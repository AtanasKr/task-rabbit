<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('task_statuses')->insert([
            ['name' => 'In Progress', 'sort_order' => 1],
            ['name' => 'Completed',  'sort_order' => 2],
            ['name' => 'Closed',     'sort_order' => 3],
        ]);
    }

    public function down(): void
    {
        DB::table('task_statuses')->whereIn('name', [
            'In Progress', 'Completed', 'Closed'
        ])->delete();
    }
};