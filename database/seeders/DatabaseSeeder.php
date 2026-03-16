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
            'name'     => 'Admin User',
            'email'    => 'admin@app.com',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Create a sample agent to link to the agent user
        $agent = Agent::create([
            'name'        => 'John Dela Cruz',
            'employee_id' => 'EMP-001',
            'department'  => 'IT Technical Support',
            'email'       => 'agent@example.com',
            'is_active'   => true,
        ]);

        // Agent user — scoped access to /agent panel
        User::factory()->create([
            'name'     => 'John Dela Cruz',
            'email'    => 'agent@example.com',
            'password' => Hash::make('password'),
            'role'     => 'agent',
            'agent_id' => $agent->id,
        ]);

        $this->call([
            IssueTypeSeeder::class,
            QuestionSeeder::class,
            LocationSeeder::class,
        ]);
    }
}
