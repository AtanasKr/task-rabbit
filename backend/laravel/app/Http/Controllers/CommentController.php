<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Task $task)
    {
        $comments = $task->comments()->with('user:id,name')->orderBy('created_at', 'asc')->get();

        return response()->json($comments, 200);
    }

    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return response()->json($comment, 201);
    }
}
