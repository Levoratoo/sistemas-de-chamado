<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            'Almoxarifado',
            'Atendimento/CS',
            'Compras',
            'Contábil',
            'DP (Departamento Pessoal)',
            'Engenharia',
            'Expedição',
            'Facilities/Manutenção',
            'Financeiro',
            'Fiscal',
            'Jurídico',
            'Logística',
            'Marketing',
            'Operações/Produção',
            'PMO/Projetos',
            'Produto',
            'Qualidade',
            'RH',
            'Segurança do Trabalho',
            'Suprimentos',
            'TI',
        ];

        foreach ($areas as $name) {
            Area::firstOrCreate(
                ['name' => $name],
                ['active' => true]
            );
        }
    }
}

