<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_list_all_users()
    {
        $user = User::factory()->create(['role' => 'admin']);
        User::factory()->count(3)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(4); // 3 + the authenticated user
    }

    #[Test]
    public function can_search_users_by_name_email_or_role()
    {
        $user = User::factory()->create(['role' => 'admin']);

        User::factory()->create([
            'name' => 'Alice Wonderland',
            'email' => 'alice@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Bob Builder',
            'email' => 'bob@example.com',
            'role' => 'user',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/users?search=Alice');

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Alice Wonderland']);
    }

    #[Test]
    public function admin_can_delete_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Test]
    public function non_admin_cannot_delete_user()
    {
        $user = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/users/{$otherUser->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $otherUser->id]);
    }

    #[Test]
    public function non_admin_sees_only_users_in_their_project()
    {
        $user = User::factory()->create(['role' => 'user']);
        $project = Project::factory()->create();
        $project->members()->attach($user->id);

        $member = User::factory()->create();
        $project->members()->attach($member->id);

        $otherProject = Project::factory()->create();
        $otherUser = User::factory()->create();
        $otherProject->members()->attach($otherUser->id);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(2); // $user + $member
        $response->assertJsonFragment(['id' => $user->id]);
        $response->assertJsonFragment(['id' => $member->id]);
        $response->assertJsonMissing(['id' => $otherUser->id]);
    }

    #[Test]
    public function non_admin_search_only_within_their_project()
    {
        $user = User::factory()->create(['role' => 'user']);
        $project = Project::factory()->create();
        $project->members()->attach($user->id);

        $member1 = User::factory()->create(['name' => 'Alice']);
        $member2 = User::factory()->create(['name' => 'Bob']);
        $project->members()->attach([$member1->id, $member2->id]);

        $otherProject = Project::factory()->create();
        $otherUser = User::factory()->create(['name' => 'Alice']);
        $otherProject->members()->attach($otherUser->id);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/users?search=Alice');

        $response->assertStatus(200);
        $response->assertJsonCount(1); // Only Alice from the same project
        $response->assertJsonFragment(['id' => $member1->id]);
        $response->assertJsonMissing(['id' => $otherUser->id]);
    }
}