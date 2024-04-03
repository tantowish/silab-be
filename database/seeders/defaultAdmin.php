<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class defaultAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create(
            [
            'username' => 'admin',
            'first_name' => 'Admin',
            'last_name' => 'SI Portofolio TA',
            'email' => 'urohmahannisa2@gmail.com',
            'password' => bcrypt('123abc'),
            'role' => 'admin',
            'email_verified_at' => '2021-08-17 00:00:00',
            ]
            );
    }
}
