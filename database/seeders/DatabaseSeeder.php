<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create a default Staff User for testing
        $user = User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'John Staff',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Create the linked Staff Profile
        // This is essential because our Claims table depends on staff_id
        if ($user->staff()->count() == 0) {
            Staff::create([
                'user_id' => $user->id,
                'department' => 'Operations',
                'base_salary' => 3000.00,
            ]);
        }
    }
}