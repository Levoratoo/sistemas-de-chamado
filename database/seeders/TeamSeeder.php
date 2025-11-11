<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            [
                'name' => 'TI',
                'description' => 'Equipe de Tecnologia da Informação'
            ],
            [
                'name' => 'ERP',
                'description' => 'Equipe de Sistemas ERP'
            ],
            [
                'name' => 'Automação',
                'description' => 'Equipe de Automação Industrial'
            ],
        ];

        foreach ($teams as $team) {
            Team::firstOrCreate(['name' => $team['name']], $team);
        }
    }
}











