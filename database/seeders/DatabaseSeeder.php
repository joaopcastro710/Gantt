<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Inserir templates
        $templateIds = [];
        $templates = [
            ['title' => 'Initial', 'description' => 'Initial phase'],
            ['title' => 'Design', 'description' => 'Design phase'],
            ['title' => 'Development', 'description' => 'Development phase'],
            ['title' => 'Testing', 'description' => 'Testing phase'],
            ['title' => 'Deployment', 'description' => 'Deployment phase'],
            ['title' => 'Review', 'description' => 'Review phase'],
        ];
        foreach ($templates as $template) {
            $templateIds[] = DB::table('task_templates')->insertGetId([
                'title' => $template['title'],
                'description' => $template['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Inserir ações (atribuições) para cada template
        $dates = [
            ['2025-03-20', '2025-03-25', '2025-03-25'],
            ['2025-03-25', '2025-03-30', '2025-03-30'],
            ['2025-04-01', '2025-04-15', '2025-04-15'],
            ['2025-04-16', '2025-04-20', '2025-04-20'],
            ['2025-04-21', '2025-04-25', '2025-04-25'],
            ['2025-04-26', '2025-04-30', '2025-04-30'],
        ];
        foreach ($templateIds as $i => $templateId) {
            DB::table('task_actions')->insert([
                'task_template_id' => $templateId,
                'start_date' => Carbon::parse($dates[$i][0]),
                'end_date' => Carbon::parse($dates[$i][1]),
                'deadline' => Carbon::parse($dates[$i][2]),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}