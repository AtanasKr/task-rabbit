<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class AnalyticsController extends Controller
{
    public function stats()
    {
        return response()->json([
            'tasks' => [
                'all' => Task::count(),
                'completed' => Task::whereHas('status', fn($q) => $q->where('name', 'Completed'))->count(),
                'closed' => Task::whereHas('status', fn($q) => $q->where('name', 'Closed'))->count(),
                'in_progress' => Task::whereHas('status', fn($q) => $q->where('name', 'In Progress'))->count(),
            ],
            'projects' => Project::count(),
            'users' => User::count(),
        ]);
    }
}
