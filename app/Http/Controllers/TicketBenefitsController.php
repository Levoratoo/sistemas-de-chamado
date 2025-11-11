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

class TicketBenefitsController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'beneficios')->first();
        
        return view('tickets.create-benefits', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'justificativa' => 'required|string|max:500',
            'empresa' => 'required|string|max:255',
            'nome_gestor_solicitante' => 'required|string|max:255',
            'nome_colaborador' => 'required|string|max:255',
            'tipo_beneficio' => 'required|string|max:255',
            'data_alteracao' => 'nullable|date|after_or_equal:today',
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
                    'name' => 'Benefícios',
                    'description' => 'Categoria para alterações de benefícios',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('beneficios', 'medium', $category->id);

            // Converter slug do tipo de benefício para nome legível
            $tipoBeneficioSlug = $request->input('tipo_beneficio');
            $tiposBeneficio = [
                'vale-refeicao' => 'Vale refeição',
                'vale-transporte' => 'Vale transporte',
                'kit-bebe-menino' => 'Kit Bebê - MENINO',
                'kit-bebe-menina' => 'Kit Bebê - MENINA',
                'multibeneficios' => 'Multibenefícios',
                'emprestimo-consignado' => 'Empréstimo Consignado',
                'plano-saude' => 'Plano de Saúde',
            ];
            $tipoBeneficioNome = $tiposBeneficio[$tipoBeneficioSlug] ?? ucfirst(str_replace('-', ' ', $tipoBeneficioSlug));

            // Montar descrição completa
            $fullDescription = "Justificativa: " . Sanitizer::sanitizeWithFormatting($request->input('justificativa') ?? '') . "\n\n";
            $fullDescription .= "Empresa: " . Sanitizer::sanitize($request->input('empresa')) . "\n";
            $fullDescription .= "Nome do Gestor Solicitante: " . Sanitizer::sanitize($request->input('nome_gestor_solicitante')) . "\n";
            $fullDescription .= "Nome do Colaborador: " . Sanitizer::sanitize($request->input('nome_colaborador')) . "\n";
            $fullDescription .= "Tipo de Benefício: " . $tipoBeneficioNome . "\n";
            
            if ($request->input('data_alteracao')) {
                $fullDescription .= "Data da Alteração: " . \Carbon\Carbon::parse($request->input('data_alteracao'))->format('d/m/Y') . "\n";
            }

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Benefícios - ' . Sanitizer::sanitize($request->input('nome_colaborador')),
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'beneficios',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_alteracao') ? 
                    \Carbon\Carbon::parse($request->input('data_alteracao')) : 
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
                    'tipo_beneficio' => $tipoBeneficioNome,
                    'data_alteracao' => $request->input('data_alteracao'),
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de benefícios criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
