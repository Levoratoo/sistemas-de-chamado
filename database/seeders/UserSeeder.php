<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::all()->keyBy('name');
        $teams = Team::all()->keyBy('name');
        $areas = Area::where('active', true)->get();

        $users = [
            // Admin - vê todas as áreas
            [
                'name' => 'Administrador',
                'email' => 'admin@local',
                'login' => 'admin',
                'password' => Hash::make('password'),
                'role_id' => $roles['admin']->id,
                'team_id' => null,
                'areas' => [], // Admin vê todas
            ],
            
            // Gestores - um para cada área principal da interface
            [
                'name' => 'Gestor Financeiro',
                'email' => 'gestor.financeiro@local',
                'login' => 'gestor.financeiro',
                'password' => Hash::make('password'),
                'role_id' => $roles['gestor']->id,
                'team_id' => null,
                'areas' => ['Financeiro'],
            ],
            [
                'name' => 'Gestor TI',
                'email' => 'gestor.ti@local',
                'login' => 'gestor.ti',
                'password' => Hash::make('password'),
                'role_id' => $roles['gestor']->id,
                'team_id' => $teams['TI']->id ?? null,
                'areas' => ['TI'],
            ],
            [
                'name' => 'Gestor Compras',
                'email' => 'gestor.compras@local',
                'login' => 'gestor.compras',
                'password' => Hash::make('password'),
                'role_id' => $roles['gestor']->id,
                'team_id' => null,
                'areas' => ['Compras'],
            ],
            [
                'name' => 'Gestor Gente e Gestão',
                'email' => 'gestor.rh@local',
                'login' => 'gestor.rh',
                'password' => Hash::make('password'),
                'role_id' => $roles['gestor']->id,
                'team_id' => null,
                'areas' => ['RH'],
            ],
            [
                'name' => 'Gestor Pré Impressão',
                'email' => 'gestor.preimpressao@local',
                'login' => 'gestor.preimpressao',
                'password' => Hash::make('password'),
                'role_id' => $roles['gestor']->id,
                'team_id' => null,
                'areas' => ['Produto'],
            ],
            [
                'name' => 'Gestor RR - Reclamações',
                'email' => 'gestor.reclamacoes@local',
                'login' => 'gestor.reclamacoes',
                'password' => Hash::make('password'),
                'role_id' => $roles['gestor']->id,
                'team_id' => null,
                'areas' => ['Logística'],
            ],
            
            // Atendentes - um para cada área principal
            [
                'name' => 'Atendente Financeiro',
                'email' => 'atendente.financeiro@local',
                'login' => 'atendente.financeiro',
                'password' => Hash::make('password'),
                'role_id' => $roles['atendente']->id,
                'team_id' => null,
                'areas' => ['Financeiro'],
            ],
            [
                'name' => 'Atendente TI',
                'email' => 'atendente.ti@local',
                'login' => 'atendente.ti',
                'password' => Hash::make('password'),
                'role_id' => $roles['atendente']->id,
                'team_id' => $teams['TI']->id ?? null,
                'areas' => ['TI'],
            ],
            [
                'name' => 'Atendente Compras',
                'email' => 'atendente.compras@local',
                'login' => 'atendente.compras',
                'password' => Hash::make('password'),
                'role_id' => $roles['atendente']->id,
                'team_id' => null,
                'areas' => ['Compras'],
            ],
            [
                'name' => 'Atendente RH',
                'email' => 'atendente.rh@local',
                'login' => 'atendente.rh',
                'password' => Hash::make('password'),
                'role_id' => $roles['atendente']->id,
                'team_id' => null,
                'areas' => ['RH'],
            ],
            [
                'name' => 'Atendente Pré Impressão',
                'email' => 'atendente.preimpressao@local',
                'login' => 'atendente.preimpressao',
                'password' => Hash::make('password'),
                'role_id' => $roles['atendente']->id,
                'team_id' => null,
                'areas' => ['Produto'],
            ],
            [
                'name' => 'Atendente Reclamações',
                'email' => 'atendente.reclamacoes@local',
                'login' => 'atendente.reclamacoes',
                'password' => Hash::make('password'),
                'role_id' => $roles['atendente']->id,
                'team_id' => null,
                'areas' => ['Logística'],
            ],
            
            // Usuários finais
            [
                'name' => 'Usuario',
                'email' => 'usuario@local',
                'login' => 'usuario',
                'password' => Hash::make('password'),
                'role_id' => $roles['usuario']->id,
                'team_id' => null,
                'areas' => [],
            ],
        ];

        foreach ($users as $userData) {
            $areaNames = $userData['areas'] ?? [];
            unset($userData['areas']);
            
            // Usar login como chave única, já que é único no banco
            $user = User::updateOrCreate(
                ['login' => $userData['login']],
                $userData
            );
            
            // Vincular áreas
            if (!empty($areaNames)) {
                $areaIds = $areas->filter(function ($area) use ($areaNames) {
                    return in_array($area->name, $areaNames);
                })->pluck('id')->toArray();
                
                if (!empty($areaIds)) {
                    $user->areas()->sync($areaIds);
                }
            }
        }
    }
}


