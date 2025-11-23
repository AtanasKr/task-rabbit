<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Task::with(['project', 'status', 'assignee', 'creator']);

        if ($request->boolean('assigned_only', false) || $user->role !== 'admin') {
            $query->where('assigned_to_id', $user->id);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        $query->orderBy('due_date', 'asc');

        $tasks = $query->get();

        return response()->json([
            'data' => $tasks
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'due_date' => 'required|date',
            'status_id' => 'required',
            'created_by_id' => 'required',
            'assigned_to_id' => 'required'
        ]);

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    public function show(Task $task)
    {
        $task->load([
            'project:id,name',
            'status:id,name',
            'assignee:id,name,email',
            'creator:id,name,email',
            'comments.user:id,name'
        ]);

        return response()->json($task, 200);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update($validated);

        return response()->json($task, 200);
    }

    public function assignTask(Request $request, TaskService $taskService)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'comment' => 'nullable|string',
        ]);

        $task = Task::findOrFail($validated['task_id']);
        $task = $taskService->assignTask($task, $validated['user_id'], $validated['comment'], $request->user()->id);

        return response()->json([
            'message' => 'Task assigned successfully',
            'task' => $task
        ], 200);
    }

    public function markComplete(Task $task)
    {
        $completedStatus = TaskStatus::where('name', 'Completed')->first();

        $task->update(['status_id' => $completedStatus->id, 'completed_at' => now()]);

        return response()->json([
            'message' => 'Task marked as complete',
            'task' => $task->load(['project', 'status', 'assignee', 'creator'])
        ], 200);
    }

    public function close(Task $task)
    {
        $closedStatus = TaskStatus::where('name', 'Closed')->first();

        if (!$closedStatus) {
            return response()->json(['message' => 'Closed status not found'], 404);
        }

        $task->update(['status_id' => $closedStatus->id]);

        return response()->json([
            'message' => 'Task closed successfully',
            'task' => $task->load(['project', 'status', 'assignee', 'creator'])
        ], 200);
    }

}
