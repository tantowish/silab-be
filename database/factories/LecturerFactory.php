<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Lecturer;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\=Lecturer>
 */
class LecturerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Lecturer::class;
    
    public function definition(): array
    {
        return [
            'id_user' => User::factory()->create([
                'role' => 'Dosen',
            ])->id,
            'image_profile' => $this->faker->imageUrl(),
            'full_name' => $this->faker->name(),
            'front_title' => $this->faker->word(1),
            'back_title' => $this->faker->word(1),
            'NID' => $this->faker->numberBetween(1, 10),
            'phone_number' => $this->faker->phoneNumber(),
            'max_quota' => $this->faker->numberBetween(1, 10),
            'isKaprodi' => 0,
        ];
    }
}
