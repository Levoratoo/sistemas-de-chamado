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

class TicketPayrollController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'lancamentos-folha')->first();
        
        return view('tickets.create-payroll', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'justificativa' => 'required|string|max:500',
            'empresa' => 'required|string|max:255',
            'nome_gestor_solicitante' => 'nullable|string|max:255',
            'tipo_lancamento' => 'required|string|max:255',
            'descricao_lancamento' => 'required|string|max:255',
            'periodo_lancamento' => 'required|string|max:255',
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
                    'name' => 'Lançamentos da Folha',
                    'description' => 'Categoria para lançamentos da folha',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('lancamentos-folha', 'medium', $category->id);

            // Converter slugs para nomes legíveis
            $tipoLancamentoSlug = $request->input('tipo_lancamento');
            $tiposLancamento = [
                'lancamento-pagar' => 'Lançamento na folha à pagar',
                'lancamento-descontar' => 'Lançamento na folha à descontar',
            ];
            $tipoLancamentoNome = $tiposLancamento[$tipoLancamentoSlug] ?? ucfirst(str_replace('-', ' ', $tipoLancamentoSlug));

            $descricaoLancamentoSlug = $request->input('descricao_lancamento');
            $descricoesLancamento = [
                'bonus-produtividade' => 'Bônus de Produtividade',
                'comissao-vendas' => 'Comissão de Vendas',
                'hora-extra' => 'Hora Extra',
                'adicional-noturno' => 'Adicional Noturno',
                'adicional-periculosidade' => 'Adicional de Periculosidade',
                'vale-refeicao' => 'Vale Refeição',
                'vale-transporte' => 'Vale Transporte',
                'plano-saude' => 'Plano de Saúde',
                'emprestimo-consignado' => 'Empréstimo Consignado',
                'faltas' => 'Faltas',
                'atrasos' => 'Atrasos',
                'desconto-adiantamento' => 'Desconto de Adiantamento',
                'outros' => 'Outros',
            ];
            $descricaoLancamentoNome = $descricoesLancamento[$descricaoLancamentoSlug] ?? ucfirst(str_replace('-', ' ', $descricaoLancamentoSlug));

            $periodoLancamentoSlug = $request->input('periodo_lancamento');
            $periodosLancamento = [
                'janeiro' => 'Janeiro',
                'fevereiro' => 'Fevereiro',
                'marco' => 'Março',
                'abril' => 'Abril',
                'maio' => 'Maio',
                'junho' => 'Junho',
                'julho' => 'Julho',
                'agosto' => 'Agosto',
                'setembro' => 'Setembro',
                'outubro' => 'Outubro',
                'novembro' => 'Novembro',
                'dezembro' => 'Dezembro',
            ];
            $periodoLancamentoNome = $periodosLancamento[$periodoLancamentoSlug] ?? ucfirst($periodoLancamentoSlug);

            // Montar descrição completa
            $fullDescription = "Justificativa: " . Sanitizer::sanitizeWithFormatting($request->input('justificativa') ?? '') . "\n\n";
            $fullDescription .= "Empresa: " . Sanitizer::sanitize($request->input('empresa')) . "\n";
            
            if ($request->input('nome_gestor_solicitante')) {
                $fullDescription .= "Nome do Gestor Solicitante: " . Sanitizer::sanitize($request->input('nome_gestor_solicitante')) . "\n";
            }
            
            $fullDescription .= "Tipo de Lançamento: " . $tipoLancamentoNome . "\n";
            $fullDescription .= "Descrição do Lançamento: " . $descricaoLancamentoNome . "\n";
            $fullDescription .= "Período de Lançamento: " . $periodoLancamentoNome . "\n";

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Lançamentos da Folha - ' . $descricaoLancamentoNome,
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'lancamentos-folha',
                'respond_by' => $slaData['respond_by'],
                'due_at' => now()->addDays(7), // SLA de 7 dias
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
                    'tipo_lancamento' => $tipoLancamentoNome,
                    'descricao_lancamento' => $descricaoLancamentoNome,
                    'periodo_lancamento' => $periodoLancamentoNome,
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Lançamento da folha criado com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}