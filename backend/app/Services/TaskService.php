<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    public function getTasks(array $filters, $user)
    {
        $query = Task::with(['project', 'status', 'assignee', 'creator']);

        if (!empty($filters['assigned_only'])) {
            $query->where('assigned_to_id', $user->id);
        }

        if ($user->role !== 'admin') {
            $query->where(function (Builder $q) use ($user) {
                $q->where('assigned_to_id', $user->id)
                    ->orWhere('created_by_id', $user->id)
                    ->orWhereHas(
                        'project.members',
                        fn($m) => $m->where('users.id', $user->id)
                    );
            });
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        return $query->orderBy('due_date')->get();
    }


    public function assignTask(Task $task, int $userId, ?string $comment, int $assignerId)
    {
        $this->authorizeAssignment($task, $userId);

        $task->update(['assigned_to_id' => $userId]);

        if ($comment) {
            $task->comments()->create([
                'user_id' => $assignerId,
                'body' => $comment,
            ]);
        }

        return $task;
    }

    public function markComplete(Task $task, $user)
    {
        $this->authorizeAccess($task, $user);

        $status = TaskStatus::where('name', 'Completed')->firstOrFail();

        $task->update([
            'status_id' => $status->id,
            'completed_at' => now(),
        ]);

        return $task->load(['project', 'status', 'assignee', 'creator']);
    }

    public function closeTask(Task $task)
    {
        $status = TaskStatus::where('name', 'Closed')->firstOrFail();

        $task->update(['status_id' => $status->id]);

        return $task->load(['project', 'status', 'assignee', 'creator']);
    }

    public function authorizeAccess(Task $task, $user)
    {
        $isProjectMember = $task->project->members()
            ->where('users.id', $user->id)->exists();

        $isAssignee = $task->assigned_to_id === $user->id;
        $isCreator = $task->created_by_id === $user->id;

        if ($user->role !== 'admin' && !$isProjectMember && !$isAssignee && !$isCreator) {
            abort(403, 'You are not authorized to access this task.');
        }
    }

    public function authorizeAssignment(Task $task, int $assigneeId)
    {
        $currentUser = Auth::user();

        $isProjectMember = $task->project->members()
            ->where('users.id', $currentUser->id)
            ->exists();

        $isCreator = $task->created_by_id === $currentUser->id;

        if ($currentUser->role !== 'admin' && !$isProjectMember && !$isCreator) {
            abort(403, 'You are not authorized to assign this task.');
        }
        $isAssigneeMember = $task->project->members()
            ->where('users.id', $assigneeId)
            ->exists();

        if (!$isAssigneeMember) {
            abort(422, 'The selected user is not a member of this project.');
        }
    }
}
