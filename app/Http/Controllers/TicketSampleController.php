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

class TicketSampleController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'solicitacao-amostra')->first();
        
        return view('tickets.create-sample', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'motivo' => 'required|string|max:255',
            'cliente' => 'required|string|max:255',
            'setor' => 'required|string|max:255',
            'descricao' => 'required|string',
            'medidas' => 'nullable|string|max:255',
            'cor_impressao' => 'nullable|string|max:255',
            'acabamento' => 'nullable|string|max:255',
            'acondicionamento' => 'nullable|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'fornecedor' => 'nullable|string|max:255',
            'data_desejada' => 'required|date|after_or_equal:today',
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
                    'name' => 'Solicitação de Amostra',
                    'description' => 'Categoria para solicitações de amostra',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('solicitacao-amostra', 'medium', $category->id);

            // Montar descrição completa
            $fullDescription = Sanitizer::sanitizeWithFormatting($request->input('descricao') ?? '');
            if ($request->input('medidas')) {
                $fullDescription .= "\n\nMedidas: " . Sanitizer::sanitize($request->input('medidas'));
            }
            if ($request->input('cor_impressao')) {
                $fullDescription .= "\nCor/Impressão: " . Sanitizer::sanitize($request->input('cor_impressao'));
            }
            if ($request->input('acabamento')) {
                $fullDescription .= "\nAcabamento: " . Sanitizer::sanitize($request->input('acabamento'));
            }
            if ($request->input('acondicionamento')) {
                $fullDescription .= "\nAcondicionamento: " . Sanitizer::sanitize($request->input('acondicionamento'));
            }
            if ($request->input('observacao')) {
                $fullDescription .= "\n\nObservações: " . Sanitizer::sanitizeWithFormatting($request->input('observacao') ?? '');
            }

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => Sanitizer::sanitize('Solicitação de Amostra - ' . $request->input('cliente')),
                'description' => $fullDescription,
                'area_id' => $comprasArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'solicitacao-amostra',
                'respond_by' => $slaData['respond_by'],
                'due_at' => \Carbon\Carbon::parse($request->input('data_desejada')),
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
                    'motivo' => $request->input('motivo'),
                    'cliente' => $request->input('cliente'),
                    'setor' => $request->input('setor'),
                    'medidas' => $request->input('medidas'),
                    'cor_impressao' => $request->input('cor_impressao'),
                    'acabamento' => $request->input('acabamento'),
                    'acondicionamento' => $request->input('acondicionamento'),
                    'quantidade' => $request->input('quantidade'),
                    'fornecedor' => $request->input('fornecedor'),
                    'data_desejada' => $request->input('data_desejada'),
                ]
            );

            // Notificar área Compras
            $notificationService->notifyNewTicketInQueue($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de amostra criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}
