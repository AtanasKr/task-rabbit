<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function index_returns_comments_for_task()
    {
        $task = Task::factory()
            ->has(Comment::factory()->count(3)->forUser())
            ->create();

        $user = $task->comments->first()->user;

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/tasks/{$task->id}/comments");

        $response->assertStatus(200);
        $response->assertJsonCount(3);

        $response->assertJsonStructure([
            '*' => [
                'id',
                'body',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
    }

    #[Test]
    public function store_creates_new_comment_safely()
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();

        $payload = [
            'body' => 'This is a test comment',
        ];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/comments", $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'body' => 'This is a test comment',
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);

        $response->assertJsonStructure([
            'id',
            'body',
            'task_id',
            'user_id',
            'created_at',
            'updated_at',
        ]);
    }

    public function user_can_create_comment()
    {
        $user = User::factory()->create(['role' => 'user']);
        $task = Task::factory()->create();

        $payload = ['body' => 'User test comment'];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/comments", $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'body' => 'User test comment',
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);
    }

    #[Test]
    public function admin_can_create_comment()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $task = Task::factory()->create();

        $payload = ['body' => 'Admin test comment'];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/comments", $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'body' => 'Admin test comment',
            'user_id' => $admin->id,
            'task_id' => $task->id,
        ]);
    }

    #[Test]
    public function comment_requires_body()
    {
        $user = User::factory()->create(['role' => 'user']);
        $task = Task::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/tasks/{$task->id}/comments", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');
    }
}
