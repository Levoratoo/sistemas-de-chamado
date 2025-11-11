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

class TicketSupplierController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'cadastro-fornecedor')->first();
        
        return view('tickets.create-supplier', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo_cadastro' => 'required|in:fornecedor,transportadora',
            'fisica_juridica_exportacao' => 'required|in:fisica,juridica,exportacao',
            'razao_social' => 'required|string|max:255',
            'cnpj_cpf' => 'required|string|max:20',
            'telefone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'endereco' => 'required|string|max:255',
            'logradouro_numero' => 'required|string|max:255',
            'bairro' => 'required|string|max:255',
            'cep' => ['required', 'string', 'max:10', 'regex:/^\d{8}$/'],
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'pais' => 'required|string|max:255',
            'inscricao_estadual' => 'nullable|string|max:255',
            'inscricao_municipal' => 'nullable|string|max:255',
            'contato' => 'nullable|string|max:255',
            'data_prevista' => 'nullable|date|after_or_equal:today',
            'descricao' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ]);

        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $user, $slaService, $notificationService) {
            // Buscar área Compras
            $comprasArea = Area::where('name', 'LIKE', '%Compras%')->first();
            if (!$comprasArea) {
                $comprasArea = Area::first(); // Fallback
            }

            // Buscar categoria padrão
            $category = Category::where('active', true)->first();
            if (!$category) {
                $category = Category::create([
                    'name' => 'Cadastro de Fornecedor/Transportadora',
                    'description' => 'Categoria para cadastro de fornecedores e transportadoras',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('cadastro-fornecedor', 'medium', $category->id);

            // Montar descrição completa
            $fullDescription = "Tipo de Cadastro: " . ($request->input('tipo_cadastro') === 'fornecedor' ? 'Fornecedor' : 'Transportadora') . "\n";
            $fullDescription .= "Tipo: " . ucfirst($request->input('fisica_juridica_exportacao')) . "\n";
            $fullDescription .= "Razão Social: " . Sanitizer::sanitize($request->input('razao_social')) . "\n";
            $fullDescription .= "CNPJ/CPF: " . Sanitizer::sanitize($request->input('cnpj_cpf')) . "\n";
            $fullDescription .= "Telefone: " . Sanitizer::sanitize($request->input('telefone')) . "\n";
            $fullDescription .= "E-mail: " . Sanitizer::sanitize($request->input('email')) . "\n";
            $fullDescription .= "\nEndereço:\n";
            $fullDescription .= Sanitizer::sanitize($request->input('endereco')) . "\n";
            $fullDescription .= Sanitizer::sanitize($request->input('logradouro_numero')) . "\n";
            $fullDescription .= "Bairro: " . Sanitizer::sanitize($request->input('bairro')) . "\n";
            $fullDescription .= "CEP: " . Sanitizer::sanitize($request->input('cep')) . "\n";
            $fullDescription .= "Cidade: " . Sanitizer::sanitize($request->input('cidade')) . "\n";
            $fullDescription .= "Estado: " . Sanitizer::sanitize($request->input('estado')) . "\n";
            $fullDescription .= "País: " . Sanitizer::sanitize($request->input('pais'));
            
            if ($request->input('inscricao_estadual')) {
                $fullDescription .= "\nInscrição Estadual: " . Sanitizer::sanitize($request->input('inscricao_estadual'));
            }
            if ($request->input('inscricao_municipal')) {
                $fullDescription .= "\nInscrição Municipal: " . Sanitizer::sanitize($request->input('inscricao_municipal'));
            }
            if ($request->input('contato')) {
                $fullDescription .= "\nContato: " . Sanitizer::sanitize($request->input('contato'));
            }
            if ($request->input('descricao')) {
                $fullDescription .= "\n\nDescrição:\n" . Sanitizer::sanitizeWithFormatting($request->input('descricao') ?? '');
            }

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => Sanitizer::sanitize($request->input('titulo')),
                'description' => $fullDescription,
                'area_id' => $comprasArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'cadastro-fornecedor',
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
                    'tipo_cadastro' => $request->input('tipo_cadastro'),
                    'fisica_juridica_exportacao' => $request->input('fisica_juridica_exportacao'),
                    'razao_social' => $request->input('razao_social'),
                    'cnpj_cpf' => $request->input('cnpj_cpf'),
                    'cidade' => $request->input('cidade'),
                    'estado' => $request->input('estado'),
                    'pais' => $request->input('pais'),
                ]
            );

            // Notificar área Compras
            $notificationService->notifyNewTicketInQueue($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de cadastro de fornecedor/transportadora criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
