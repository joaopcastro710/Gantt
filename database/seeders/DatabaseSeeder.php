<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tasks')->insert([
            [
                'title' => 'Initial',
                'start_date' => Carbon::parse('2025-03-20'),
                'end_date' => Carbon::parse('2025-03-25'),
                'deadline' => Carbon::parse('2025-03-25'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Design',
                'start_date' => Carbon::parse('2025-03-25'),
                'end_date' => Carbon::parse('2025-03-30'),
                'deadline' => Carbon::parse('2025-03-30'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Add more tasks here...
        ]);
    }
}
