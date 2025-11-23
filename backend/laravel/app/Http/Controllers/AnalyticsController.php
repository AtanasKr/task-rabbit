<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class AnalyticsController extends Controller
{
    public function stats()
    {
        $taskCounts = Task::query()
            ->leftJoin('task_statuses', 'tasks.status_id', '=', 'task_statuses.id')
            ->selectRaw("
                COUNT(*) as all_tasks,
                SUM(CASE WHEN task_statuses.name = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN task_statuses.name = 'Closed' THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN task_statuses.name = 'In Progress' THEN 1 ELSE 0 END) as in_progress
            ")
            ->first();

        return response()->json([
            'tasks' => [
                'all' => $taskCounts->all_tasks,
                'completed' => $taskCounts->completed,
                'closed' => $taskCounts->closed,
                'in_progress' => $taskCounts->in_progress,
            ],
            'projects' => Project::count(),
            'users' => User::count(),
        ]);
    }
}
