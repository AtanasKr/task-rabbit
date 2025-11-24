<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request, TaskService $service)
    {
        $tasks = $service->getTasks($request->all(), $request->user());

        return response()->json(['data' => $tasks], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'due_date' => 'required|date',
            'assigned_to_id' => 'required'
        ]);

        $validated['created_by_id'] = $request->user()->id;
        $validated['status_id'] = TaskStatus::firstWhere('name', 'In Progress')->id;

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    public function show(Request $request, Task $task, TaskService $service)
    {
        $service->authorizeAccess($task, $request->user());

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
        $task->update($request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status_id' => 'sometimes|required|exists:task_statuses,id',
            'assigned_to_id' => 'sometimes|required|exists:users,id',
            'project_id' => 'sometimes|required|exists:projects,id',
        ]));

        return response()->json($task, 200);
    }

    public function assignTask(Request $request, TaskService $service)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'comment' => 'nullable|string',
        ]);

        $task = Task::findOrFail($validated['task_id']);

        $task = $service->assignTask(
            $task,
            $validated['user_id'],
            $validated['comment'],
            $request->user()->id
        );

        return response()->json([
            'message' => 'Task assigned successfully',
            'task' => $task
        ], 200);
    }

    public function markComplete(Request $request, Task $task, TaskService $service)
    {
        $task = $service->markComplete($task, $request->user());

        return response()->json([
            'message' => 'Task marked as complete',
            'task' => $task
        ], 200);
    }

    public function close(Task $task, TaskService $service)
    {
        $task = $service->closeTask($task);

        return response()->json([
            'message' => 'Task closed successfully',
            'task' => $task
        ], 200);
    }
}
