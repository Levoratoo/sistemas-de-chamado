<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Sla;
use Illuminate\Database\Seeder;

class SlaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $priorities = ['low', 'medium', 'high', 'critical'];
        
        foreach ($categories as $category) {
            foreach ($priorities as $priority) {
                $responseTime = $this->getResponseTime($priority);
                $resolveTime = $this->getResolveTime($priority);
                
                Sla::firstOrCreate([
                    'category_id' => $category->id,
                    'priority' => $priority,
                ], [
                    'name' => "SLA {$category->name} - " . ucfirst($priority),
                    'response_time_minutes' => $responseTime,
                    'resolve_time_minutes' => $resolveTime,
                    'active' => true,
                ]);
            }
        }
    }

    private function getResponseTime(string $priority): int
    {
        return match($priority) {
            'low' => 240,      // 4 horas
            'medium' => 120,   // 2 horas
            'high' => 60,      // 1 hora
            'critical' => 15,   // 15 minutos
            default => 120
        };
    }

    private function getResolveTime(string $priority): int
    {
        return match($priority) {
            'low' => 1440,     // 24 horas
            'medium' => 480,   // 8 horas
            'high' => 240,     // 4 horas
            'critical' => 60,  // 1 hora
            default => 480
        };
    }
}











