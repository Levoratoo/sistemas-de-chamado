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
use Illuminate\Support\Facades\Storage;

class TicketAccessController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'liberacao-acessos')->first();
        
        return view('tickets.create-access', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'motivo' => 'required|string|max:255',
            'necessidades' => 'required|string',
            'niveis_acesso' => 'required|array|min:1',
            'niveis_acesso.*' => 'in:consulta,edicao,exclusao',
            'impacto' => 'nullable|in:baixo,medio,alto,critico',
            'data_para_ficar_pronto' => 'nullable|date|after:today',
            'empresa' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $user, $slaService, $notificationService) {
            // Buscar área TI
            $tiArea = Area::where('name', 'LIKE', '%TI%')->first();
            if (!$tiArea) {
                $tiArea = Area::first(); // Fallback
            }

            // Buscar categoria padrão
            $category = Category::where('active', true)->first();
            if (!$category) {
                $category = Category::create([
                    'name' => 'Liberação de Acessos',
                    'description' => 'Categoria para liberação de acessos',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('liberacao-acessos', $request->input('impacto', 'medium'), $category->id);

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => Sanitizer::sanitize($request->input('motivo')),
                'description' => Sanitizer::sanitizeWithFormatting($request->input('necessidades') ?? ''),
                'area_id' => $tiArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => $request->input('impacto', 'medium'),
                'request_type' => 'liberacao-acessos',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_para_ficar_pronto') ? 
                    \Carbon\Carbon::parse($request->input('data_para_ficar_pronto')) : 
                    $slaData['due_at'],
                'last_status_change_at' => now(),
            ]);

            // Log do evento
            TicketEventService::log($ticket, $user, 'created', null, null, [
                'niveis_acesso' => $request->input('niveis_acesso'),
                'empresa' => $request->input('empresa'),
                'data_para_ficar_pronto' => $request->input('data_para_ficar_pronto'),
            ]);

            // Notificar área TI
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Chamado de Liberação de Acessos criado com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}