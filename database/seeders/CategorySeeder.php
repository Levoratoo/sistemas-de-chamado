<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'TI',
                'description' => 'Problemas relacionados a Tecnologia da Informação',
                'active' => true
            ],
            [
                'name' => 'ERP',
                'description' => 'Problemas relacionados ao sistema ERP',
                'active' => true
            ],
            [
                'name' => 'Automação',
                'description' => 'Problemas relacionados a sistemas de automação',
                'active' => true
            ],
            [
                'name' => 'Financeiro',
                'description' => 'Problemas relacionados a sistemas financeiros',
                'active' => true
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}











