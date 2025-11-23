<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\AnalyticsController;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function stats_returns_correct_json(): void
    {
        // Seed users, projects, and tasks
        $users = User::factory()->count(3)->create();
        $projects = Project::factory()->count(2)->create();

        // Create tasks with different statuses: 1=in_progress, 2=completed, 3=closed
        Task::factory()->create(['status_id' => 1]);
        Task::factory()->create(['status_id' => 2]);
        Task::factory()->create(['status_id' => 2]);
        Task::factory()->create(['status_id' => 3]);

        $controller = new AnalyticsController();
        $response = $controller->stats();

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);

        $allTasks = Task::count();
        $completed = Task::where('status_id', 2)->count();
        $closed = Task::where('status_id', 3)->count();
        $inProgress = Task::where('status_id', 1)->count();
        $projectCount = Project::count();
        $userCount = User::count();

        $this->assertEquals($allTasks, $data['tasks']['all']);
        $this->assertEquals($completed, $data['tasks']['completed']);
        $this->assertEquals($closed, $data['tasks']['closed']);
        $this->assertEquals($inProgress, $data['tasks']['in_progress']);
        $this->assertEquals($projectCount, $data['projects']);
        $this->assertEquals($userCount, $data['users']);
    }
}
