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
use App\Helpers\DepartmentHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketJobOpeningController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'abertura-vaga')->first();
        
        return view('tickets.create-job-opening', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'motivo_admissao' => 'required|in:aumento-quadro,substituicao',
            'nome_colaborador_substituido' => 'nullable|string|max:255',
            'empresa' => 'required|string|max:255',
            'tipo_recrutamento' => 'required|in:externo,interno,misto',
            'tipo_contrato' => 'required|in:clt,pj,estagio,jovem-aprendiz',
            'departamento' => 'required|string|max:255',
            'cargo' => 'required|string|max:255',
            'escala_trabalho' => 'required|string|max:255',
            'data_prevista_contratacao' => 'nullable|date|after_or_equal:today',
            'observacao' => 'nullable|string',
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
                    'name' => 'Abertura de Vaga',
                    'description' => 'Categoria para abertura de vagas',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('abertura-vaga', 'medium', $category->id);

            // Montar descrição completa
            $fullDescription = "Motivo da Admissão: " . ($request->input('motivo_admissao') === 'aumento-quadro' ? 'Aumento de quadro' : 'Substituição') . "\n";
            if ($request->input('motivo_admissao') === 'substituicao' && $request->input('nome_colaborador_substituido')) {
                $fullDescription .= "Colaborador Substituído: " . Sanitizer::sanitize($request->input('nome_colaborador_substituido')) . "\n";
            }
            $fullDescription .= "Empresa: " . Sanitizer::sanitize($request->input('empresa')) . "\n";
            $fullDescription .= "Tipo de Recrutamento: " . ucfirst($request->input('tipo_recrutamento')) . "\n";
            $tipoContratoLabels = [
                'clt' => 'CLT',
                'pj' => 'PJ',
                'estagio' => 'Estágio',
                'jovem-aprendiz' => 'Jovem Aprendiz',
            ];
            $tipoContrato = $tipoContratoLabels[$request->input('tipo_contrato')] ?? $request->input('tipo_contrato');
            $fullDescription .= "Tipo de Contrato: " . $tipoContrato . "\n";
            $departamentoNome = DepartmentHelper::slugToName($request->input('departamento'));
            $fullDescription .= "Departamento: " . $departamentoNome . "\n";
            
            // Converter slug do cargo para nome legível (remove hífens e coloca em maiúsculas)
            $cargoSlug = $request->input('cargo');
            $cargoNome = strtoupper(str_replace('-', ' ', $cargoSlug));
            $fullDescription .= "Cargo: " . $cargoNome . "\n";
            $fullDescription .= "Escala de Trabalho: " . Sanitizer::sanitize($request->input('escala_trabalho')) . "\n";
            
            if ($request->input('data_prevista_contratacao')) {
                $fullDescription .= "Data Prevista: " . \Carbon\Carbon::parse($request->input('data_prevista_contratacao'))->format('d/m/Y') . "\n";
            }
            
            if ($request->input('observacao')) {
                $fullDescription .= "\nObservações:\n" . Sanitizer::sanitizeWithFormatting($request->input('observacao') ?? '');
            }

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => Sanitizer::sanitize($request->input('titulo')),
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'abertura-vaga',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_prevista_contratacao') ? 
                    \Carbon\Carbon::parse($request->input('data_prevista_contratacao')) : 
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
                    'motivo_admissao' => $request->input('motivo_admissao'),
                    'nome_colaborador_substituido' => $request->input('nome_colaborador_substituido'),
                    'empresa' => $request->input('empresa'),
                    'tipo_recrutamento' => $request->input('tipo_recrutamento'),
                    'tipo_contrato' => $request->input('tipo_contrato'),
                    'departamento' => $request->input('departamento'),
                    'cargo' => $request->input('cargo'),
                    'escala_trabalho' => $request->input('escala_trabalho'),
                    'data_prevista_contratacao' => $request->input('data_prevista_contratacao'),
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyNewTicketInQueue($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de abertura de vaga criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
