<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // fake 20 managers role 2 of User model
        \App\Models\User::factory()->count(20)->create([
            'role' => \App\Models\User::ROLE_ADMIN,
        ]);
    }
}
