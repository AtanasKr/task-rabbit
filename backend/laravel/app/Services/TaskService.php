<?php
namespace App\Services;

use App\Models\Task;

class TaskService
{
    public function assignTask(Task $task, int $userId, ?string $comment, $assignerId)
    {
        $task->assigned_to = $userId;
        $task->save();

        if ($comment) {
            $task->comments()->create([
                'user_id' => $assignerId,
                'body' => $comment,
            ]);
        }

        return $task;
    }
}