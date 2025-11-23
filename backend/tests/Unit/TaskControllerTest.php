<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        TaskStatus::firstOrCreate(['name' => 'In Progress'], ['sort_order' => 1]);
        TaskStatus::firstOrCreate(['name' => 'Completed'], ['sort_order' => 2]);
        TaskStatus::firstOrCreate(['name' => 'Closed'], ['sort_order' => 3]);
    }

    #[Test]
    public function admin_can_view_all_tasks()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Task::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/tasks');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    #[Test]
    public function user_can_only_see_assigned_or_member_tasks()
    {
        $user = User::factory()->create(['role' => 'user']);
        $project = Project::factory()->create();
        $task1 = Task::factory()->create(['assigned_to_id' => $user->id]);
        $task2 = Task::factory()->create(['project_id' => $project->id]);

        $project->members()->attach($user->id);

        $response = $this->actingAs($user)->getJson('/api/tasks');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function user_can_mark_task_complete()
    {
        $user = User::factory()->create(['role' => 'user']);
        $task = Task::factory()->create(['assigned_to_id' => $user->id]);

        $response = $this->actingAs($user)
            ->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status_id' => TaskStatus::firstWhere('name', 'Completed')->id,
        ]);
    }

    #[Test]
    public function admin_can_mark_task_complete()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $task = Task::factory()->create(['assigned_to_id' => $user->id]);

        $response = $this->actingAs($user)
            ->patchJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status_id' => TaskStatus::firstWhere('name', 'Completed')->id,
        ]);
    }

    #[Test]
    public function admin_can_create_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::factory()->create();
        $assignee = User::factory()->create();

        $payload = [
            'title' => 'New Task',
            'description' => 'Test task',
            'project_id' => $project->id,
            'due_date' => '2025-01-01',
            'assigned_to_id' => $assignee->id,
        ];

        $response = $this->actingAs($admin)->postJson('/api/tasks', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
    }

    #[Test]
    public function admin_can_update_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $task = Task::factory()->create();

        $response = $this->actingAs($admin)
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Task',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', ['title' => 'Updated Task']);
    }

    #[Test]
    public function admin_can_close_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $task = Task::factory()->create();

        $response = $this->actingAs($admin)
            ->patchJson("/api/tasks/{$task->id}/close");

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status_id' => TaskStatus::firstWhere('name', 'Closed')->id,
        ]);
    }
}