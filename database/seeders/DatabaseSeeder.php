<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user — full access to /admin panel
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@app.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create a sample HR personnel record to link to the HR user
        $agent = Agent::create([
            'name' => 'Jane Dela Cruz',
            'employee_id' => 'HR-001',
            'department' => 'Human Resources',
            'email' => 'hr@example.com',
            'is_active' => true,
        ]);

        // HR personnel login — scoped access to /agent panel
        User::factory()->create([
            'name' => 'Jane Dela Cruz',
            'email' => 'hr@example.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'agent_id' => $agent->id,
        ]);

        $this->call([
            SettingSeeder::class,
            IssueTypeSeeder::class,
            QuestionSeeder::class,
            LocationSeeder::class,
        ]);
    }
}
