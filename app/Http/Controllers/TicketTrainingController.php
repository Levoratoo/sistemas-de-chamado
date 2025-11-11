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

class TicketTrainingController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'solicitacao-treinamento')->first();
        
        return view('tickets.create-training', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'justificativa' => 'required|string|max:500',
            'empresa' => 'required|string|max:255',
            'nome_gestor_solicitante' => 'required|string|max:255',
            'nome_colaborador' => 'required|string|max:255',
            'tipo_treinamento' => 'nullable|string|max:255',
            'local_treinamento' => 'nullable|string|max:255',
            'observacao' => 'nullable|string|max:1000',
            'data_prevista' => 'nullable|date|after_or_equal:today',
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
                    'name' => 'Treinamento',
                    'description' => 'Categoria para solicitação de treinamento',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('solicitacao-treinamento', 'medium', $category->id);

            // Converter slug do tipo de treinamento para nome legível
            $tipoTreinamentoSlug = $request->input('tipo_treinamento');
            $tiposTreinamento = [
                'nenhum' => 'Nenhum',
                'atividades-cargo-funcao' => 'Treinamento para a realização de atividades no cargo/função',
                'desenvolver-habilidades' => 'Treinamento para desenvolver habilidades',
            ];
            $tipoTreinamentoNome = $tiposTreinamento[$tipoTreinamentoSlug] ?? ($tipoTreinamentoSlug ? ucfirst(str_replace('-', ' ', $tipoTreinamentoSlug)) : '');

            // Converter slug do local de treinamento para nome legível
            $localTreinamentoSlug = $request->input('local_treinamento');
            $locaisTreinamento = [
                'nenhum' => 'Nenhum',
                'interno-grupo' => 'Interno - Grupo Weisul',
                'externo-consultoria' => 'Externo - Consultoria/Fornecedores',
            ];
            $localTreinamentoNome = $locaisTreinamento[$localTreinamentoSlug] ?? ($localTreinamentoSlug ? ucfirst(str_replace('-', ' ', $localTreinamentoSlug)) : '');

            // Montar descrição completa
            $fullDescription = "Justificativa: " . Sanitizer::sanitizeWithFormatting($request->input('justificativa') ?? '') . "\n\n";
            $fullDescription .= "Empresa: " . Sanitizer::sanitize($request->input('empresa')) . "\n";
            $fullDescription .= "Nome do Gestor Solicitante: " . Sanitizer::sanitize($request->input('nome_gestor_solicitante')) . "\n";
            $fullDescription .= "Nome do Colaborador: " . Sanitizer::sanitize($request->input('nome_colaborador')) . "\n";
            
            if ($tipoTreinamentoNome) {
                $fullDescription .= "Tipo de Treinamento: " . $tipoTreinamentoNome . "\n";
            }
            
            if ($localTreinamentoNome) {
                $fullDescription .= "Local do Treinamento: " . $localTreinamentoNome . "\n";
            }
            
            if ($request->input('observacao')) {
                $fullDescription .= "\nObservação: " . Sanitizer::sanitizeWithFormatting($request->input('observacao') ?? '') . "\n";
            }
            
            if ($request->input('data_prevista')) {
                $fullDescription .= "\nData Prevista: " . \Carbon\Carbon::parse($request->input('data_prevista'))->format('d/m/Y') . "\n";
            }

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Solicitação de Treinamento - ' . Sanitizer::sanitize($request->input('nome_colaborador')),
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'solicitacao-treinamento',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_prevista') ? 
                    \Carbon\Carbon::parse($request->input('data_prevista')) : 
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
            TicketEventService::log(
                $ticket,
                $user,
                'created',
                null,
                'open',
                [
                    'justificativa' => $request->input('justificativa'),
                    'empresa' => $request->input('empresa'),
                    'nome_gestor_solicitante' => $request->input('nome_gestor_solicitante'),
                    'nome_colaborador' => $request->input('nome_colaborador'),
                    'tipo_treinamento' => $tipoTreinamentoNome,
                    'local_treinamento' => $localTreinamentoNome,
                    'observacao' => $request->input('observacao'),
                    'data_prevista' => $request->input('data_prevista'),
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de treinamento criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
