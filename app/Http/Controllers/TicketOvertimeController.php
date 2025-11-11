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

class TicketOvertimeController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'solicitacao-hora-extra')->first();
        
        return view('tickets.create-overtime', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
            'empresa' => 'required|string|max:255',
            'gestor' => 'required|string|max:255',
            'nome_colaborador' => 'nullable|string|max:255',
            'colaboradores_arquivo' => 'nullable|array',
            'colaboradores_arquivo.*' => 'file|max:10240', // 10MB max
            'periodo_hora_extra' => 'required|date|after_or_equal:tomorrow', // Pelo menos 1 dia de antecedência
            'horario_hora_extra' => 'required|string|max:255',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ]);

        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $user, $slaService, $notificationService) {
            // Buscar área Gente e Gestão
            $rhArea = Area::where('name', 'LIKE', '%Gente%')
                ->orWhere('name', 'LIKE', '%Gestão%')
                ->orWhere('name', 'LIKE', '%RH%')
                ->first();
            if (!$rhArea) {
                $rhArea = Area::first(); // Fallback
            }

            // Buscar categoria padrão
            $category = Category::where('active', true)->first();
            if (!$category) {
                $category = Category::create([
                    'name' => 'Hora Extra',
                    'description' => 'Categoria para solicitação de hora extra',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('solicitacao-hora-extra', 'medium', $category->id);

            // Montar descrição completa
            $fullDescription = "Motivo: " . Sanitizer::sanitizeWithFormatting($request->input('motivo') ?? '') . "\n\n";
            $fullDescription .= "Empresa: " . Sanitizer::sanitize($request->input('empresa')) . "\n";
            $fullDescription .= "Gestor: " . Sanitizer::sanitize($request->input('gestor')) . "\n";
            
            if ($request->input('nome_colaborador')) {
                $fullDescription .= "Nome do Colaborador: " . Sanitizer::sanitize($request->input('nome_colaborador')) . "\n";
            }
            
            $fullDescription .= "Período da Hora Extra: " . \Carbon\Carbon::parse($request->input('periodo_hora_extra'))->format('d/m/Y') . "\n";
            $fullDescription .= "Horário da Hora Extra: " . Sanitizer::sanitize($request->input('horario_hora_extra')) . "\n";

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Solicitação Hora Extra' . ($request->input('nome_colaborador') ? ' - ' . Sanitizer::sanitize($request->input('nome_colaborador')) : ''),
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'solicitacao-hora-extra',
                'respond_by' => $slaData['respond_by'],
                'due_at' => \Carbon\Carbon::parse($request->input('periodo_hora_extra')),
                'last_status_change_at' => now(),
            ]);

            // Processar anexo de colaboradores (se houver)
            if ($request->hasFile('colaboradores_arquivo')) {
                foreach ($request->file('colaboradores_arquivo') as $file) {
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

            // Processar outros anexos
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
            TicketEventService::log(
                $ticket,
                $user,
                'created',
                null,
                'open',
                [
                    'motivo' => $request->input('motivo'),
                    'empresa' => $request->input('empresa'),
                    'gestor' => $request->input('gestor'),
                    'nome_colaborador' => $request->input('nome_colaborador'),
                    'periodo_hora_extra' => $request->input('periodo_hora_extra'),
                    'horario_hora_extra' => $request->input('horario_hora_extra'),
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de hora extra criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
