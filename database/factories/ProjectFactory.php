<?php

namespace Database\Factories;

use App\Models\Lecturer;
use App\Models\Period;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Project::class;
    public function definition(): array
    {
        return [
            'id_lecturer' => Lecturer::factory()->create()->id,
            'id_period' => Period::factory()->create()->id,
            'tittle' => $this->faker->sentence(1),
            'agency' => $this->faker->company(1),
            'description'=> $this->faker->sentence(5),
            'tools'=> $this->faker->sentence(1),
            'status' => $this->faker->randomElement([0, 1]),
        ];
    }
}
