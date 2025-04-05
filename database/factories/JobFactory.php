<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class JobFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->paragraph,
            'company_name' => $this->faker->company,
            'salary_min' => $this->faker->randomFloat(2, 30000, 50000),
            'salary_max' => $this->faker->randomFloat(2, 60000, 120000),
            'is_remote' => $this->faker->boolean,
            'job_type' => $this->faker->randomElement(['full-time', 'part-time', 'contract', 'freelance']),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'published_at' => Carbon::now(),
        ];
    }
}
