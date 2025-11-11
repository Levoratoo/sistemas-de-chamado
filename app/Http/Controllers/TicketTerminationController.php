<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Area;
use App\Models\Category;
use App\Models\RequestType;
use App\Models\User;
use App\Services\SlaCalculationService;
use App\Services\TicketEventService;
use App\Services\NotificationService;
use App\Helpers\Sanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketTerminationController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'solicitacao-desligamento')->first();
        
        return view('tickets.create-termination', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'resumo' => 'required|string|max:500',
            'empresa' => 'required|string|max:255',
            'nome_gestor_solicitante' => 'required|string|max:255',
            'nome_colaborador' => 'required|string|max:255',
            'tipo_desligamento' => 'required|string|max:255',
            'data_desligamento' => 'nullable|date|after_or_equal:today',
            'cancelamento_acessos' => 'nullable|string',
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
                    'name' => 'Solicitação de Desligamento',
                    'description' => 'Categoria para solicitação de desligamento',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('solicitacao-desligamento', 'medium', $category->id);

            // Montar descrição completa
            $fullDescription = "Resumo: " . Sanitizer::sanitize($request->input('resumo')) . "\n\n";
            $fullDescription .= "Empresa: " . Sanitizer::sanitize($request->input('empresa')) . "\n";
            $fullDescription .= "Nome do Gestor Solicitante: " . Sanitizer::sanitize($request->input('nome_gestor_solicitante')) . "\n";
            $fullDescription .= "Nome do Colaborador: " . Sanitizer::sanitize($request->input('nome_colaborador')) . "\n";
            // Converter slug do tipo de desligamento para nome legível
            $tipoDesligamentoSlug = $request->input('tipo_desligamento');
            $tiposDesligamento = [
                'demissao-empregador-aviso-indenizado' => 'Demissão pelo EMPREGADOR - AVISO INDENIZADO',
                'demissao-empregador-aviso-trabalhado' => 'Demissão pelo EMPREGADOR - AVISO TRABALHADO',
                'pedido-demissao-aviso-indenizado' => 'Pedido de Demissão - AVISO INDENIZADO',
                'pedido-demissao-aviso-trabalhado' => 'Pedido de Demissão - AVISO TRABALHADO',
                'termino-automatico-experiencia-colaborador' => 'Término AUTOMÁTICO Contrato Experiência pelo COLABORADOR',
                'termino-automatico-experiencia-empresa' => 'Término AUTOMÁTICO Contrato Experiência pela EMPRESA',
                'termino-antecipado-experiencia-colaborador' => 'Término ANTECIPADO Contrato Experiência pelo COLABORADOR',
                'termino-antecipado-experiencia-empresa' => 'Término ANTECIPADO Contrato Experiência pela EMPRESA',
                'demissao-justa-causa' => 'Demissão POR JUSTA CAUSA',
            ];
            $tipoDesligamentoNome = $tiposDesligamento[$tipoDesligamentoSlug] ?? $tipoDesligamentoSlug;
            $fullDescription .= "Tipo de Desligamento: " . $tipoDesligamentoNome . "\n";
            
            if ($request->input('data_desligamento')) {
                $fullDescription .= "Data de Desligamento: " . \Carbon\Carbon::parse($request->input('data_desligamento'))->format('d/m/Y') . "\n";
            }
            
            if ($request->input('cancelamento_acessos')) {
                $fullDescription .= "\nCancelamento de Acessos:\n" . Sanitizer::sanitizeWithFormatting($request->input('cancelamento_acessos') ?? '') . "\n";
            }


            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Solicitação de Desligamento - ' . Sanitizer::sanitize($request->input('nome_colaborador')),
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'solicitacao-desligamento',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_desligamento') ? 
                    \Carbon\Carbon::parse($request->input('data_desligamento')) : 
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
                    'empresa' => $request->input('empresa'),
                    'nome_gestor_solicitante' => $request->input('nome_gestor_solicitante'),
                    'nome_colaborador' => $request->input('nome_colaborador'),
                    'tipo_desligamento' => $tipoDesligamentoNome,
                    'data_desligamento' => $request->input('data_desligamento'),
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de desligamento criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
