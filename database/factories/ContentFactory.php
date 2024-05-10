<?php

namespace Database\Factories;

use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;

class ContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Content::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id_proyek' => Project::factory()->create()->id,
            'thumbnail_image_url' => $this->faker->imageUrl(),
            'content_url'   => $this->faker->imageUrl(),
            'video_url'    => $this->faker->url(),
            'video_tittle' => $this->faker->sentence(1),
            'github_url'  => $this->faker->url(),
            'tipe_konten' => $this->faker->randomElement(['jurnal', 'tugas akhir']),
        ];
    }
}
