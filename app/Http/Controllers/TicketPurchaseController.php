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

class TicketPurchaseController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'solicitacao-compra')->first();
        
        return view('tickets.create-purchase', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'centro_custo' => 'required|string|max:255',
            'setor' => 'required|string|max:255',
            'descricao_item' => 'required|string',
            'codigo_item' => 'nullable|string|max:255',
            'quantidade' => 'nullable|integer|min:1',
            'data_desejada' => 'nullable|date|after_or_equal:today',
            'aprovador' => 'nullable|string|max:255',
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
                    'name' => 'Solicitação de Compra',
                    'description' => 'Categoria para solicitações de compra',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('solicitacao-compra', 'medium', $category->id);

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => Sanitizer::sanitize($request->input('titulo')),
                'description' => Sanitizer::sanitizeWithFormatting(($request->input('descricao_item') ?? '') . 
                    ($request->input('observacao') ? "\n\nObservações: " . $request->input('observacao') : '')),
                'area_id' => $comprasArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'solicitacao-compra',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_desejada') ? 
                    \Carbon\Carbon::parse($request->input('data_desejada')) : 
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
                    'setor' => $request->input('setor'),
                    'codigo_item' => $request->input('codigo_item'),
                    'quantidade' => $request->input('quantidade'),
                    'data_desejada' => $request->input('data_desejada'),
                    'aprovador' => $request->input('aprovador'),
                ]
            );

            // Notificar área Compras
            $notificationService->notifyNewTicketInQueue($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de compra criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
