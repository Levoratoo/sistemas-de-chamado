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
use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketRRLController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'rrl-reclamacao-logistica')->first();
        
        return view('tickets.create-rrl', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        // Validar se anexo é obrigatório para Troca Parcial ou Total
        $ocorrencia = $request->input('ocorrencia');
        $requiresAttachment = in_array($ocorrencia, ['troca-parcial', 'troca-total']);
        
        $rules = [
            'resumo' => 'required|string|max:255',
            'ocorrencia' => 'required|string|max:255',
            'produto' => 'required|string|max:2000',
            'nome_cliente' => 'required|string|max:255',
            'codigo_cliente' => 'required|string|max:50',
            'contato' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'nota_fiscal_remessa' => 'nullable|string|max:50',
            'nota_fiscal_fatura' => 'nullable|string|max:50',
            'tipo_reclamacao' => 'nullable|array',
            'tipo_reclamacao.*' => 'string|max:255',
            'descricao' => 'required|string|max:5000',
            'acao_imediata_financeiro' => 'nullable|string|max:2000',
            'acao_imediata_logistica' => 'nullable|string|max:2000',
            'transportadora' => 'nullable|string|max:255',
            'prioridade' => 'nullable|string|in:critica,planejado',
            'data_ficar_pronto' => 'nullable|date|after_or_equal:today',
            'attachments' => $requiresAttachment ? 'required|array|min:1' : 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ];
        
        $messages = [
            'attachments.required' => 'O campo ANEXO é obrigatório para "Troca Parcial" ou "Troca Total"!',
            'attachments.min' => 'Pelo menos um anexo é obrigatório para "Troca Parcial" ou "Troca Total"!',
        ];
        
        $request->validate($rules, $messages);

        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $user, $slaService, $notificationService) {
            // Buscar área de Logística ou RR
            $area = Area::where('name', 'LIKE', '%Logística%')
                ->orWhere('name', 'LIKE', '%RR%')
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
                    'name' => 'RRL - Reclamação Logística',
                    'description' => 'Categoria para reclamações logísticas',
                    'active' => true,
                ]);
            }

            // Calcular SLA baseado na prioridade
            $priority = $request->input('prioridade', 'planejado');
            $priorityLevel = $priority === 'critica' ? 'high' : 'medium';
            $slaData = $slaService->calculateSlaForTicket('rrl-reclamacao-logistica', $priorityLevel, $category->id);

            // Converter ocorrência slug para nome legível
            $ocorrenciaSlug = $request->input('ocorrencia');
            $ocorrencias = [
                'extravio-total' => 'Extravio Total',
                'extravio-parcial' => 'Extravio Parcial',
                'avaria' => 'Avaria',
                'troca-parcial' => 'Troca Parcial',
                'troca-total' => 'Troca Total',
                'mau-atendimento' => 'Mau Atendimento',
            ];
            $ocorrenciaNome = $ocorrencias[$ocorrenciaSlug] ?? ucfirst(str_replace('-', ' ', $ocorrenciaSlug));

            // Converter prioridade
            $prioridadeNome = $priority === 'critica' ? 'Crítica (24hs)' : 'Planejado (48hs)';

            // Montar descrição completa
            $fullDescription = "Resumo: " . Sanitizer::sanitize($request->input('resumo')) . "\n\n";
            $fullDescription .= "Ocorrência: " . $ocorrenciaNome . "\n\n";
            $fullDescription .= "Produto: " . Sanitizer::sanitizeWithFormatting($request->input('produto') ?? '') . "\n\n";
            $fullDescription .= "Nome do Cliente: " . Sanitizer::sanitize($request->input('nome_cliente')) . "\n";
            $fullDescription .= "Código do Cliente: " . Sanitizer::sanitize($request->input('codigo_cliente')) . "\n";
            
            if ($request->input('contato')) {
                $fullDescription .= "Contato: " . Sanitizer::sanitize($request->input('contato')) . "\n";
            }
            if ($request->input('telefone')) {
                $fullDescription .= "Telefone: " . Sanitizer::sanitize($request->input('telefone')) . "\n";
            }
            if ($request->input('nota_fiscal_remessa')) {
                $fullDescription .= "Nota Fiscal - Remessa: " . Sanitizer::sanitize($request->input('nota_fiscal_remessa')) . "\n";
            }
            if ($request->input('nota_fiscal_fatura')) {
                $fullDescription .= "Nota Fiscal - Fatura: " . Sanitizer::sanitize($request->input('nota_fiscal_fatura')) . "\n";
            }
            
            if ($request->has('tipo_reclamacao') && is_array($request->input('tipo_reclamacao'))) {
                $tipos = implode(', ', array_map(function($tipo) {
                    $tiposMap = [
                        'entrega' => 'Entrega',
                        'qualidade' => 'Qualidade',
                        'outros' => 'Outros',
                    ];
                    return $tiposMap[$tipo] ?? ucfirst($tipo);
                }, $request->input('tipo_reclamacao')));
                $fullDescription .= "Tipo de Reclamação: " . $tipos . "\n";
            }
            
            $fullDescription .= "\nDescrição: " . Sanitizer::sanitizeWithFormatting($request->input('descricao') ?? '') . "\n";
            
            if ($request->input('acao_imediata_financeiro')) {
                $fullDescription .= "\nAção Imediata - Financeiro:\n" . Sanitizer::sanitizeWithFormatting($request->input('acao_imediata_financeiro') ?? '') . "\n";
            }
            if ($request->input('acao_imediata_logistica')) {
                $fullDescription .= "\nAção Imediata - Logística:\n" . Sanitizer::sanitizeWithFormatting($request->input('acao_imediata_logistica') ?? '') . "\n";
            }
            if ($request->input('transportadora')) {
                $fullDescription .= "\nTransportadora: " . Sanitizer::sanitize($request->input('transportadora')) . "\n";
            }
            
            $fullDescription .= "\nPrioridade: " . $prioridadeNome . "\n";
            
            if ($request->input('data_ficar_pronto')) {
                $fullDescription .= "Data para Ficar Pronto: " . \Carbon\Carbon::parse($request->input('data_ficar_pronto'))->format('d/m/Y') . "\n";
            }


            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'RRL - ' . Sanitizer::sanitize($request->input('resumo')),
                'description' => $fullDescription,
                'area_id' => $area->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => $priorityLevel,
                'request_type' => 'rrl-reclamacao-logistica',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_ficar_pronto') ? 
                    \Carbon\Carbon::parse($request->input('data_ficar_pronto')) : 
                    $slaData['due_at'],
                'last_status_change_at' => now(),
            ]);

            // Processar anexos
            if ($request->hasFile('attachments')) {
                FileUploadHelper::processAttachments(
                    $ticket,
                    $request->file('attachments'),
                    $user->id
                );
            }

            // Log do evento
            TicketEventService::log(
                $ticket,
                $user,
                'created',
                null,
                'open',
                [
                    'resumo' => $request->input('resumo'),
                    'ocorrencia' => $ocorrenciaNome,
                    'nome_cliente' => $request->input('nome_cliente'),
                    'codigo_cliente' => $request->input('codigo_cliente'),
                    'prioridade' => $prioridadeNome,
                ]
            );

            // Notificar área de Logística
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'RRL criado com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
