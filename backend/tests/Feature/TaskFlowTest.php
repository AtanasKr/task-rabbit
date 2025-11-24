<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TaskFlowTest extends TestCase
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
    public function all_users_can_create_and_view_task()
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $project = Project::factory()->create();
        $assignee = User::factory()->create();

        $payload = [
            'title'          => 'Feature Test Task',
            'description'    => 'Created from feature test',
            'project_id'     => $project->id,
            'due_date'       => '2025-01-01',
            'assigned_to_id' => $assignee->id,
        ];

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/tasks', $payload);

        $createResponse->assertStatus(201);
        $createResponse->assertJsonFragment([
            'title' => 'Feature Test Task',
        ]);

        $taskId = $createResponse->json('id');

        $this->assertDatabaseHas('tasks', [
            'id'    => $taskId,
            'title' => 'Feature Test Task',
        ]);

        $showResponse = $this->actingAs($admin)
            ->getJson("/api/tasks/{$taskId}");

        $showResponse->assertStatus(200);
        $showResponse->assertJsonFragment([
            'id'    => $taskId,
            'title' => 'Feature Test Task',
        ]);

        $createResponseUser = $this->actingAs($user)
            ->postJson('/api/tasks', $payload);

        $createResponseUser->assertStatus(201);
        $createResponseUser->assertJsonFragment([
            'title' => 'Feature Test Task',
        ]);

        $taskIdUser = $createResponseUser->json('id');

        $this->assertDatabaseHas('tasks', [
            'id'    => $taskIdUser,
            'title' => 'Feature Test Task',
        ]);
    }
}
