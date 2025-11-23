<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_all_projects()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Project::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/projects');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    #[Test]
    public function non_admin_sees_only_projects_where_they_are_member()
    {
        $user = User::factory()->create(['role' => 'user']);

        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        $project1->members()->attach($user->id);

        $response = $this->actingAs($user)->getJson('/api/projects');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    #[Test]
    public function can_search_projects_by_name_or_description()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Project::factory()->create(['name' => 'Alpha']);
        Project::factory()->create(['name' => 'Beta']);

        $response = $this->actingAs($admin)->getJson('/api/projects?search=Alpha');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    #[Test]
    public function admin_can_create_project()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $payload = [
            'name' => 'New Project',
            'description' => 'Test',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-10',
        ];

        $response = $this->actingAs($admin)->postJson('/api/projects', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', ['name' => 'New Project']);
    }

    #[Test]
    public function non_admin_cannot_view_project_they_do_not_belong_to()
    {
        $user = User::factory()->create(['role' => 'user']);
        $project = Project::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function user_can_view_project_if_member()
    {
        $user = User::factory()->create(['role' => 'user']);
        $project = Project::factory()->create();

        $project->members()->attach($user->id);

        $response = $this->actingAs($user)->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
    }

    #[Test]
    public function admin_can_update_project()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::factory()->create();

        $response = $this->actingAs($admin)->putJson("/api/projects/{$project->id}", [
            'name' => 'Updated Name',
            'end_date' => $project->start_date,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', ['name' => 'Updated Name']);
    }

    #[Test]
    public function admin_can_delete_project()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    #[Test]
    public function admin_can_add_members_to_project()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/projects/{$project->id}/members", [
                'user_ids' => [$user1->id, $user2->id],
            ]);


        $response->assertStatus(200);

        $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $user1->id,
        ]);
    }

    #[Test]
    public function admin_can_remove_members_from_project()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $project = Project::factory()->create();

        $user = User::factory()->create();
        $project->members()->attach($user->id);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/projects/{$project->id}/members", [
                'user_ids' => [$user->id],
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);
    }
}
