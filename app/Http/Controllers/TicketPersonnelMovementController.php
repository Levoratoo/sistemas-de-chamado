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
use App\Helpers\DepartmentHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketPersonnelMovementController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'movimentacao-pessoal')->first();
        
        // Carregar usuários para busca
        $users = User::with('role')->orderBy('name')->get();
        
        return view('tickets.create-personnel-movement', compact('areas', 'categories', 'requestType', 'users'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'motivo' => 'required|string|max:500',
            'nome_colaborador' => 'nullable|string|max:255',
            'empresa' => 'required|string|max:255',
            'nome_gestor_solicitante' => 'required|string|max:255',
            'cargo_atual' => 'nullable|string|max:255',
            'nivel_atual' => 'nullable|string|max:255',
            'cargo_proposto' => 'nullable|string|max:255',
            'nivel_proposto' => 'nullable|string|max:255',
            'escala_trabalho_atual' => 'nullable|string|max:255',
            'escala_trabalho_proposto' => 'nullable|string|max:255',
            'departamento_atual' => 'nullable|string|max:255',
            'departamento_proposto' => 'nullable|string|max:255',
            'salario_atual' => 'nullable|string|max:255',
            'salario_proposto' => 'nullable|string|max:255',
            'data_alteracao' => 'nullable|date|after_or_equal:today',
            'aprovacoes' => 'nullable|string',
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
                    'name' => 'Movimentação de Pessoal',
                    'description' => 'Categoria para movimentação de pessoal',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('movimentacao-pessoal', 'medium', $category->id);

            // Montar descrição completa
            $fullDescription = "Motivo: " . Sanitizer::sanitize($request->input('motivo')) . "\n\n";
            
            if ($request->input('nome_colaborador')) {
                $fullDescription .= "Nome do Colaborador: " . Sanitizer::sanitize($request->input('nome_colaborador')) . "\n";
            }
            $fullDescription .= "Empresa: " . Sanitizer::sanitize($request->input('empresa')) . "\n";
            $fullDescription .= "Nome do Gestor Solicitante: " . Sanitizer::sanitize($request->input('nome_gestor_solicitante')) . "\n\n";
            
            $fullDescription .= "SITUAÇÃO ATUAL:\n";
            if ($request->input('cargo_atual')) {
                $cargoAtualNome = strtoupper(str_replace('-', ' ', $request->input('cargo_atual')));
                $fullDescription .= "Cargo: " . $cargoAtualNome . "\n";
            }
            if ($request->input('nivel_atual')) {
                $fullDescription .= "Nível: " . Sanitizer::sanitize($request->input('nivel_atual')) . "\n";
            }
            if ($request->input('escala_trabalho_atual')) {
                $fullDescription .= "Escala de Trabalho: " . Sanitizer::sanitize($request->input('escala_trabalho_atual')) . "\n";
            }
            if ($request->input('departamento_atual')) {
                $departamentoAtualNome = DepartmentHelper::slugToName($request->input('departamento_atual'));
                $fullDescription .= "Departamento: " . $departamentoAtualNome . "\n";
            }
            if ($request->input('salario_atual')) {
                $fullDescription .= "Salário: " . Sanitizer::sanitize($request->input('salario_atual')) . "\n";
            }
            
            $fullDescription .= "\nSITUAÇÃO PROPOSTA:\n";
            if ($request->input('cargo_proposto')) {
                $cargoPropostoNome = strtoupper(str_replace('-', ' ', $request->input('cargo_proposto')));
                $fullDescription .= "Cargo: " . $cargoPropostoNome . "\n";
            }
            if ($request->input('nivel_proposto')) {
                $fullDescription .= "Nível: " . Sanitizer::sanitize($request->input('nivel_proposto')) . "\n";
            }
            if ($request->input('escala_trabalho_proposto')) {
                $fullDescription .= "Escala de Trabalho: " . Sanitizer::sanitize($request->input('escala_trabalho_proposto')) . "\n";
            }
            if ($request->input('departamento_proposto')) {
                $departamentoPropostoNome = DepartmentHelper::slugToName($request->input('departamento_proposto'));
                $fullDescription .= "Departamento: " . $departamentoPropostoNome . "\n";
            }
            if ($request->input('salario_proposto')) {
                $fullDescription .= "Salário: " . Sanitizer::sanitize($request->input('salario_proposto')) . "\n";
            }
            
            if ($request->input('data_alteracao')) {
                $fullDescription .= "\nData Prevista: " . \Carbon\Carbon::parse($request->input('data_alteracao'))->format('d/m/Y') . "\n";
            }
            
            // Processar aprovações
            $aprovacoesIds = [];
            if ($request->input('aprovacoes')) {
                $aprovacoesIds = json_decode($request->input('aprovacoes'), true) ?? [];
                if (!empty($aprovacoesIds)) {
                    $aprovadores = User::whereIn('id', $aprovacoesIds)->pluck('name')->toArray();
                    $fullDescription .= "\nAprovadores: " . implode(', ', $aprovadores) . "\n";
                }
            }

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Movimentação de Pessoal' . ($request->input('nome_colaborador') ? ' - ' . Sanitizer::sanitize($request->input('nome_colaborador')) : ''),
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'movimentacao-pessoal',
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
                    'nome_colaborador' => $request->input('nome_colaborador'),
                    'empresa' => $request->input('empresa'),
                    'nome_gestor_solicitante' => $request->input('nome_gestor_solicitante'),
                    'cargo_atual' => $request->input('cargo_atual'),
                    'cargo_proposto' => $request->input('cargo_proposto'),
                    'departamento_atual' => $request->input('departamento_atual'),
                    'departamento_proposto' => $request->input('departamento_proposto'),
                    'data_alteracao' => $request->input('data_alteracao'),
                    'aprovacoes' => $aprovacoesIds,
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de movimentação de pessoal criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
