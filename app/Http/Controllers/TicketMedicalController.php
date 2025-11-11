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
use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketMedicalController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'atestados-declaracoes-medicas')->first();
        
        return view('tickets.create-medical', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'tipo_documento' => 'required|string|in:atestado-medico,declaracao-medica',
            'nome_colaborador' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'turno' => 'nullable|string|max:255',
            'periodo_dias' => 'nullable|integer|min:1|required_without:periodo_horas',
            'periodo_horas' => 'nullable|integer|min:1|required_without:periodo_dias',
            'data_documento' => 'required|date|before_or_equal:today',
            'cid' => 'nullable|string|max:50',
            'nome_medico' => 'nullable|string|max:255',
            'registro_medico' => 'nullable|string|max:50',
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
                    'name' => 'Atestados e Declarações Médicas',
                    'description' => 'Categoria para atestados e declarações médicas',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('atestados-declaracoes-medicas', 'medium', $category->id);

            // Converter slugs para nomes legíveis
            $tipoDocumentoSlug = $request->input('tipo_documento');
            $tiposDocumento = [
                'atestado-medico' => 'Atestado Médico',
                'declaracao-medica' => 'Declaração Médica',
            ];
            $tipoDocumentoNome = $tiposDocumento[$tipoDocumentoSlug] ?? ucfirst(str_replace('-', ' ', $tipoDocumentoSlug));

            // Montar descrição completa
            $fullDescription = "Tipo de Documento: " . $tipoDocumentoNome . "\n\n";
            $fullDescription .= "Nome do Colaborador: " . Sanitizer::sanitize($request->input('nome_colaborador')) . "\n";
            $departamentoNome = DepartmentHelper::slugToName($request->input('departamento'));
            $fullDescription .= "Departamento: " . $departamentoNome . "\n";
            
            if ($request->input('turno')) {
                $turnoSlug = $request->input('turno');
                $turnos = [
                    '1-turno' => '1º turno',
                    '2-turno' => '2º turno',
                    '3-turno' => '3º turno',
                    'comercial' => 'Comercial',
                ];
                $turnoNome = $turnos[$turnoSlug] ?? ucfirst(str_replace('-', ' ', $turnoSlug));
                $fullDescription .= "Turno: " . $turnoNome . "\n";
            }
            
            if ($request->input('periodo_dias')) {
                $fullDescription .= "Período em Dias: " . $request->input('periodo_dias') . " dias\n";
            }
            
            if ($request->input('periodo_horas')) {
                $fullDescription .= "Período em Horas: " . $request->input('periodo_horas') . " horas\n";
            }
            
            $fullDescription .= "Data do Documento: " . \Carbon\Carbon::parse($request->input('data_documento'))->format('d/m/Y') . "\n";
            
            if ($request->input('cid')) {
                $fullDescription .= "CID: " . Sanitizer::sanitize($request->input('cid')) . "\n";
            }
            
            if ($request->input('nome_medico')) {
                $fullDescription .= "Nome do Médico/Dentista: " . Sanitizer::sanitize($request->input('nome_medico')) . "\n";
            }
            
            if ($request->input('registro_medico')) {
                $fullDescription .= "Registro do Médico/Dentista: " . Sanitizer::sanitize($request->input('registro_medico')) . "\n";
            }
            
            if ($request->input('observacao')) {
                $fullDescription .= "\nObservação: " . Sanitizer::sanitizeWithFormatting($request->input('observacao') ?? '') . "\n";
            }

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => $tipoDocumentoNome . ' - ' . Sanitizer::sanitize($request->input('nome_colaborador')),
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'atestados-declaracoes-medicas',
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
                    'tipo_documento' => $tipoDocumentoNome,
                    'nome_colaborador' => $request->input('nome_colaborador'),
                    'departamento' => $request->input('departamento'),
                    'turno' => $request->input('turno'),
                    'data_documento' => $request->input('data_documento'),
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Atestado/Declaração médica criado com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
