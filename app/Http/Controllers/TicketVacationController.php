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

class TicketVacationController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'solicitacao-ferias')->first();
        
        return view('tickets.create-vacation', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
            'empresa' => 'required|string|max:255',
            'nome_gestor_solicitante' => 'required|string|max:255',
            'nome_colaborador' => 'required|string|max:255',
            'periodo_aquisitivo' => 'required|string|max:255',
            'quantidade_ferias' => 'required|string|max:255',
            'data_inicio_ferias' => 'required|date|after_or_equal:today',
            'data_fim_ferias' => ['required', 'date', function ($attribute, $value, $fail) use ($request) {
                if ($request->input('data_inicio_ferias') && $value <= $request->input('data_inicio_ferias')) {
                    $fail('A data fim das férias deve ser posterior à data início.');
                }
            }],
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
                    'name' => 'Solicitação de Férias',
                    'description' => 'Categoria para solicitação de férias',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('solicitacao-ferias', 'medium', $category->id);

            // Montar descrição completa
            $fullDescription = "Motivo: " . Sanitizer::sanitize($request->input('motivo')) . "\n\n";
            $fullDescription .= "Empresa: " . Sanitizer::sanitize($request->input('empresa')) . "\n";
            $fullDescription .= "Nome do Gestor Solicitante: " . Sanitizer::sanitize($request->input('nome_gestor_solicitante')) . "\n";
            $fullDescription .= "Nome do Colaborador: " . Sanitizer::sanitize($request->input('nome_colaborador')) . "\n";
            $fullDescription .= "Período Aquisitivo: " . Sanitizer::sanitize($request->input('periodo_aquisitivo')) . "\n";
            $fullDescription .= "Quantidade de Férias: " . Sanitizer::sanitize($request->input('quantidade_ferias')) . "\n";
            $fullDescription .= "Data Início das Férias: " . \Carbon\Carbon::parse($request->input('data_inicio_ferias'))->format('d/m/Y') . "\n";
            $fullDescription .= "Data Fim das Férias: " . \Carbon\Carbon::parse($request->input('data_fim_ferias'))->format('d/m/Y') . "\n";

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Solicitação de Férias - ' . Sanitizer::sanitize($request->input('nome_colaborador')),
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'solicitacao-ferias',
                'respond_by' => $slaData['respond_by'],
                'due_at' => \Carbon\Carbon::parse($request->input('data_inicio_ferias'))->subDays(7), // 7 dias antes do início das férias
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
                    'periodo_aquisitivo' => $request->input('periodo_aquisitivo'),
                    'quantidade_ferias' => $request->input('quantidade_ferias'),
                    'data_inicio_ferias' => $request->input('data_inicio_ferias'),
                    'data_fim_ferias' => $request->input('data_fim_ferias'),
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de férias criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
