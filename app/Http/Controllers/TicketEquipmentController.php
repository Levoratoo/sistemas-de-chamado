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

class TicketEquipmentController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'equipamentos-perifericos')->first();
        
        return view('tickets.create-equipment', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'resumo' => 'required|string|max:255',
            'detalhes' => 'required|string',
            'tipo_equipamento' => 'nullable|string|max:255',
            'impacto' => 'required|in:baixo,medio,alto,critico',
            'data_para_ficar_pronto' => 'nullable|date|after:today',
            'empresa' => 'nullable|string|max:255',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
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
                    'name' => 'Equipamentos e Periféricos',
                    'description' => 'Categoria para equipamentos e periféricos',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('equipamentos-perifericos', $request->input('impacto', 'medium'), $category->id);

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => Sanitizer::sanitize($request->input('resumo')),
                'description' => Sanitizer::sanitizeWithFormatting($request->input('detalhes') ?? ''),
                'area_id' => $tiArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => $request->input('impacto', 'medium'),
                'request_type' => 'equipamentos-perifericos',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_para_ficar_pronto') ? 
                    \Carbon\Carbon::parse($request->input('data_para_ficar_pronto')) : 
                    $slaData['due_at'],
                'last_status_change_at' => now(),
            ]);

            // Processar anexos
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('tickets/' . $ticket->id);
                        
                        $ticket->attachments()->create([
                            'user_id' => $user->id,
                            'filename' => $file->getClientOriginalName(),
                            'path' => $path,
                            'mime' => $file->getMimeType(),
                            'size' => $file->getSize(),
                        ]);
                    }
                }
            }

            // Log do evento
            TicketEventService::log($ticket, $user, 'created', null, null, [
                'tipo_equipamento' => $request->input('tipo_equipamento'),
                'empresa' => $request->input('empresa'),
                'data_para_ficar_pronto' => $request->input('data_para_ficar_pronto'),
            ]);

            // Notificar área TI
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Chamado de Equipamentos e Periféricos criado com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}