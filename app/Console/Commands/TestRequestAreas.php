<?php

namespace App\Console\Commands;

use App\Models\RequestArea;
use Illuminate\Console\Command;

class TestRequestAreas extends Command
{
    protected $signature = 'test:request-areas';
    protected $description = 'Testa as áreas de solicitação';

    public function handle(): int
    {
        $this->info('🧪 Testando Áreas de Solicitação');
        
        $areas = RequestArea::with('requestTypes')->get();
        
        foreach ($areas as $area) {
            $this->line("\n📁 {$area->name} ({$area->slug})");
            $this->line("   Tipos: {$area->requestTypes->count()}");
            
            foreach ($area->requestTypes as $type) {
                $this->line("   - {$type->name} ({$type->slug})");
            }
        }
        
        $this->info("\n✅ Teste concluído!");
        return 0;
    }
}