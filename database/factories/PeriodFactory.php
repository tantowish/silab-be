<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\=Period>
 */
class PeriodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'semester' => $this->faker->randomElement(['Ganjil', 'Genap']),
            'year' => $this->faker->year(),
            'description' => $this->faker->randomElement(['Tugas Akhir', 'Yudisium']),
            'status' => $this->faker->randomElement(['Aktif', 'Tidak Aktif']),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'tanggal_sidang' => $this->faker->date(),
        ];
    }
}
