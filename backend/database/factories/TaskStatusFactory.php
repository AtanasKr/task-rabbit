<?php

namespace Database\Factories;

use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskStatusFactory extends Factory
{
    protected $model = TaskStatus::class;

    public function definition(): array
    {
        static $order = 1;

        return [
            'name' => $this->faker->unique()->word(),
            'sort_order' => $order++,
        ];
    }

    /** Preset common statuses */
    public function inProgress(): static
    {
        return $this->state(fn() => ['name' => 'In Progress', 'sort_order' => 1]);
    }

    public function completed(): static
    {
        return $this->state(fn() => ['name' => 'Completed', 'sort_order' => 2]);
    }

    public function closed(): static
    {
        return $this->state(fn() => ['name' => 'Closed', 'sort_order' => 3]);
    }
}
