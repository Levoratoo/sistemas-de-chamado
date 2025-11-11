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

class TicketItemController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'cadastro-item')->first();
        
        return view('tickets.create-item', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'centro_custo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'ncm' => 'nullable|string|max:255',
            'unidade_medida' => 'required|string|max:255',
            'data_prevista' => 'nullable|date|after_or_equal:today',
            'observacao' => 'nullable|string',
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
                    'name' => 'Cadastro de Item',
                    'description' => 'Categoria para cadastro de itens',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('cadastro-item', 'medium', $category->id);

            // Montar descrição completa
            $fullDescription = Sanitizer::sanitizeWithFormatting($request->input('descricao') ?? '');
            if ($request->input('ncm')) {
                $fullDescription .= "\n\nNCM: " . Sanitizer::sanitize($request->input('ncm'));
            }
            if ($request->input('unidade_medida')) {
                $fullDescription .= "\nUnidade de Medida: " . Sanitizer::sanitize($request->input('unidade_medida'));
            }
            if ($request->input('observacao')) {
                $fullDescription .= "\n\nObservações: " . Sanitizer::sanitizeWithFormatting($request->input('observacao') ?? '');
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
                'request_type' => 'cadastro-item',
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
                    'centro_custo' => $request->input('centro_custo'),
                    'ncm' => $request->input('ncm'),
                    'unidade_medida' => $request->input('unidade_medida'),
                    'data_prevista' => $request->input('data_prevista'),
                ]
            );

            // Notificar área Compras
            $notificationService->notifyNewTicketInQueue($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de cadastro de item criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
