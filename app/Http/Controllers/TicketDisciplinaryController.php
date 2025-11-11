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

class TicketDisciplinaryController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'medidas-disciplinares')->first();
        
        return view('tickets.create-disciplinary', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'justificativa' => 'required|string|max:500',
            'empresa' => 'required|string|max:255',
            'gestor' => 'required|string|max:255',
            'nome_colaborador' => 'required|string|max:255',
            'tipo_medida_disciplinar' => 'required|string|max:255',
            'data_aplicacao' => 'nullable|date|after_or_equal:today',
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
                    'name' => 'Medidas Disciplinares',
                    'description' => 'Categoria para medidas disciplinares',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('medidas-disciplinares', 'medium', $category->id);

            // Converter slug do tipo de medida disciplinar para nome legível
            $tipoMedidaSlug = $request->input('tipo_medida_disciplinar');
            $tiposMedida = [
                'nenhum' => 'Nenhum',
                'advertencia-verbal' => 'Advertência verbal',
                'advertencia-escrita' => 'Advertência escrita',
                'suspensao' => 'Suspensão',
            ];
            $tipoMedidaNome = $tiposMedida[$tipoMedidaSlug] ?? $tipoMedidaSlug;

            // Montar descrição completa
            $fullDescription = "Justificativa: " . Sanitizer::sanitizeWithFormatting($request->input('justificativa') ?? '') . "\n\n";
            $fullDescription .= "Empresa: " . Sanitizer::sanitize($request->input('empresa')) . "\n";
            $fullDescription .= "Gestor: " . Sanitizer::sanitize($request->input('gestor')) . "\n";
            $fullDescription .= "Nome do Colaborador: " . Sanitizer::sanitize($request->input('nome_colaborador')) . "\n";
            $fullDescription .= "Tipo da Medida Disciplinar: " . $tipoMedidaNome . "\n";
            
            if ($request->input('data_aplicacao')) {
                $fullDescription .= "Data da Aplicação: " . \Carbon\Carbon::parse($request->input('data_aplicacao'))->format('d/m/Y') . "\n";
            }

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Medidas Disciplinares - ' . Sanitizer::sanitize($request->input('nome_colaborador')),
                'description' => $fullDescription,
                'area_id' => $rhArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'medidas-disciplinares',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_aplicacao') ? 
                    \Carbon\Carbon::parse($request->input('data_aplicacao')) : 
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
                    'nome_colaborador' => $request->input('nome_colaborador'),
                    'tipo_medida_disciplinar' => $tipoMedidaNome,
                    'data_aplicacao' => $request->input('data_aplicacao'),
                ]
            );

            // Notificar área Gente e Gestão
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de medidas disciplinares criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
