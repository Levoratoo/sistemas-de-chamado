<?php

namespace Database\Seeders;

use App\Models\RequestArea;
use Illuminate\Database\Seeder;

class RequestAreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            [
                'name' => 'Financeiro',
                'slug' => 'financeiro',
                'description' => 'Solicitações relacionadas ao departamento financeiro',
                'icon' => 'currency-dollar',
                'color' => '#10B981', // Verde
                'sort_order' => 1,
            ],
            [
                'name' => 'TI',
                'slug' => 'ti',
                'description' => 'Solicitações relacionadas à tecnologia da informação',
                'icon' => 'computer-desktop',
                'color' => '#3B82F6', // Azul
                'sort_order' => 2,
            ],
            [
                'name' => 'Compras',
                'slug' => 'compras',
                'description' => 'Solicitações relacionadas ao departamento de compras',
                'icon' => 'shopping-cart',
                'color' => '#F59E0B', // Amarelo
                'sort_order' => 3,
            ],
            [
                'name' => 'Gente e Gestão',
                'slug' => 'gente-gestao',
                'description' => 'Solicitações relacionadas a recursos humanos',
                'icon' => 'users',
                'color' => '#8B5CF6', // Roxo
                'sort_order' => 4,
            ],
            [
                'name' => 'Pré Impressão',
                'slug' => 'pre-impressao',
                'description' => 'Solicitações relacionadas à pré-impressão',
                'icon' => 'printer',
                'color' => '#EF4444', // Vermelho
                'sort_order' => 5,
            ],
            [
                'name' => 'RR - Registro de Reclamações',
                'slug' => 'registro-reclamacoes',
                'description' => 'Solicitações relacionadas ao registro de reclamações',
                'icon' => 'exclamation-triangle',
                'color' => '#F97316', // Laranja
                'sort_order' => 6,
            ],
            [
                'name' => 'Geral',
                'slug' => 'geral',
                'description' => 'Solicitações gerais sem área específica',
                'icon' => 'document-text',
                'color' => '#6B7280', // Cinza
                'sort_order' => 7,
            ],
        ];

        foreach ($areas as $area) {
            RequestArea::firstOrCreate(
                ['slug' => $area['slug']],
                $area
            );
        }
    }
}