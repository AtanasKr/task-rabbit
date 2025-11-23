<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status_id' => $this->faker->numberBetween(1, 3),
            'project_id' => Project::factory(),
            'assigned_to_id' => User::factory(),
            'created_by_id' => User::factory(),
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'completed_at' => null,
        ];
    }
    
    public function assigned(User $user)
    {
        return $this->state(fn() => [
            'assigned_to_id' => $user->id,
        ]);
    }
}
