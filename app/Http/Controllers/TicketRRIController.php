<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Area;
use App\Models\Category;
use App\Models\RequestType;
use App\Services\SlaCalculationService;
use App\Services\TicketEventService;
use App\Services\NotificationService;
use App\Helpers\Sanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketRRIController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'rri-reclamacao-interna')->first();
        
        return view('tickets.create-rri', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'resumo' => 'required|string|max:255',
            'prioridade' => 'nullable|string|in:critica,planejado',
        ]);

        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $user, $slaService, $notificationService) {
            // Buscar área de RR ou Reclamações
            $area = Area::where('name', 'LIKE', '%RR%')
                ->orWhere('name', 'LIKE', '%Reclamação%')
                ->first();
            if (!$area) {
                $area = Area::where('slug', 'registro-reclamacoes')->first();
            }
            if (!$area) {
                $area = Area::first(); // Fallback
            }

            // Buscar categoria padrão
            $category = Category::where('active', true)->first();
            if (!$category) {
                $category = Category::create([
                    'name' => 'RRI - Reclamação Interna',
                    'description' => 'Categoria para reclamações internas',
                    'active' => true,
                ]);
            }

            // Calcular SLA baseado na prioridade
            $priority = $request->input('prioridade', 'planejado');
            $priorityLevel = $priority === 'critica' ? 'high' : 'medium';
            $slaData = $slaService->calculateSlaForTicket('rri-reclamacao-interna', $priorityLevel, $category->id);

            // Converter prioridade
            $prioridadeNome = $priority === 'critica' ? 'Crítica (24hs)' : 'Planejado (48hs)';

            // Montar descrição completa
            $fullDescription = "Resumo: " . Sanitizer::sanitize($request->input('resumo')) . "\n\n";
            $fullDescription .= "Prioridade: " . $prioridadeNome . "\n";

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'RRI - ' . Sanitizer::sanitize($request->input('resumo')),
                'description' => $fullDescription,
                'area_id' => $area->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => $priorityLevel,
                'request_type' => 'rri-reclamacao-interna',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $slaData['due_at'],
                'last_status_change_at' => now(),
            ]);

            // Log do evento
            TicketEventService::log(
                $ticket,
                $user,
                'created',
                null,
                'open',
                [
                    'resumo' => $request->input('resumo'),
                    'prioridade' => $prioridadeNome,
                ]
            );

            // Notificar área de RR
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'RRI criado com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
