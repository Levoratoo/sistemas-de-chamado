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

class TicketCommunicationController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'solicitacao-comunicados')->first();
        
        return view('tickets.create-communication', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'justificativa' => 'required|string|max:500',
            'empresa' => 'required|string|max:255',
            'gestor' => 'required|string|max:255',
            'tipo_comunicado' => 'nullable|string|max:255',
            'ferramentas_comunicacao' => 'nullable|array',
            'ferramentas_comunicacao.*' => 'string|max:255',
            'data_prevista' => 'nullable|date|after_or_equal:today',
            'observacao' => 'nullable|string|max:1000',
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
                    'name' => 'Comunicados',
                    'description' => 'Categoria para solicitação de comunicados',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('solicitacao-comunicados', 'medium', $category->id);

            // Converter slug do tipo de comunicado para nome legível
            $tipoComunicadoSlug = $request->input('tipo_comunicado');
            $tiposComunicado = [
                'nenhum' => 'Nenhum',
                'interno-todos-colaboradores' => 'Comunicado Interno para todos os colaboradores',
                'interno-todos-lideres' => 'Comunicado Internos para todos os líderes',
                'tv-corporativa' => 'TV Corporativa',
            ];
            $tipoComunicadoNome = $tiposComunicado[$tipoComunicadoSlug] ?? ($tipoComunicadoSlug ? ucfirst(str_replace('-', ' ', $tipoComunicadoSlug)) : '');

            // Processar ferramentas de comunicação
            $ferramentas = $request->input('ferramentas_comunicacao', []);
            $ferramentasNomes = [
                'email' => 'E-mail',
                'whatsapp' => 'WhatsApp',
                'tv-corporativa' => 'TV Corporativa',
                'dds' => 'DDS - Diálogo de Segurança',
                'outros' => 'Outros',
            ];
            $ferramentasSelecionadas = [];
            foreach ($ferramentas as $ferramenta) {
                $ferramentasSelecionadas[] = $ferramentasNomes[$ferramenta] ?? $ferramenta;
            }

            // Montar descrição completa
            $fullDescription = "Justificativa: " . Sanitizer::sanitizeWithFormatting($request->input('justificativa') ?? '') . "\n\n";
            $fullDescription .= "Empresa: " . Sanitizer::sanitize($request->input('empresa')) . "\n";
            $fullDescription .= "Gestor: " . Sanitizer::sanitize($request->input('gestor')) . "\n";
            
            if ($tipoComunicadoNome) {
                $fullDescription .= "Tipo de Comunicado: " . $tipoComunicadoNome . "\n";
            }
            
            if (!empty($ferramentasSelecionadas)) {
                $fullDescription .= "Ferramentas de Comunicação: " . implode(', ', $ferramentasSelecionadas) . "\n";
            }
            
            if ($request->input('observacao')) {
                $fullDescription .= "\nObservação: " . Sanitizer::sanitizeWithFormatting($request->input('observacao') ?? '') . "\n";
            }
            
            if ($request->input('data_prevista')) {
                $fullDescription .= "\nData Prevista: " . \Carbon\Carbon::parse($request->input('data_prevista'))->format('d/m/Y') . "\n";
            }

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Solicitação de Comunicados' . ($request->input('tipo_comunicado') && $tipoComunicadoNome ? ' - ' . $tipoComunicadoNome : ''),
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'solicitacao-comunicados',
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
                    'gestor' => $request->input('gestor'),
                    'tipo_comunicado' => $tipoComunicadoNome,
                    'ferramentas_comunicacao' => $ferramentasSelecionadas,
                    'observacao' => $request->input('observacao'),
                    'data_prevista' => $request->input('data_prevista'),
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de comunicados criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
