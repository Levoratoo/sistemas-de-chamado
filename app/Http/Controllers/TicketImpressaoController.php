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
use App\Helpers\FileUploadHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketImpressaoController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        $requestType = RequestType::where('slug', 'impressao')->first();

        return view('tickets.create-impressao', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'tipo_impressao' => 'required|string|max:100',
            'impressao_customizada' => 'nullable|string|max:255',
            'quantidade' => 'nullable|integer|min:1',
            'data_prevista' => 'nullable|date|after_or_equal:today',
            'observacao' => 'nullable|string|max:5000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:20480',
        ]);

        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $user, $slaService, $notificationService) {
            $area = Area::where('name', 'LIKE', '%Pré Impressão%')
                ->orWhere('name', 'LIKE', '%Pre Impressao%')
                ->first();
            if (!$area) {
                $area = Area::firstOrCreate(['name' => 'Pré Impressão'], ['active' => true]);
            }

            $category = Category::where('active', true)->first();
            if (!$category) {
                $category = Category::create([
                    'name' => 'Impressão',
                    'description' => 'Solicitações de impressão',
                    'active' => true,
                ]);
            }

            $slaData = $slaService->calculateSlaForTicket('impressao', 'medium', $category->id);

            $fullDescription = "Título: " . Sanitizer::sanitize($request->input('titulo')) . "\n\n";
            $fullDescription .= "Tipo de Impressão: " . Sanitizer::sanitize($request->input('tipo_impressao')) . "\n\n";
            if ($request->input('impressao_customizada')) {
                $fullDescription .= "Impressão Customizada: " . Sanitizer::sanitize($request->input('impressao_customizada')) . "\n\n";
            }
            if ($request->input('quantidade')) {
                $fullDescription .= "Quantidade de Impressões: " . (int) $request->input('quantidade') . "\n\n";
            }
            if ($request->input('data_prevista')) {
                $fullDescription .= "Data Prevista: " . \Carbon\Carbon::parse($request->input('data_prevista'))->format('d/m/Y') . "\n\n";
            }
            if ($request->input('observacao')) {
                $fullDescription .= "Observação: " . Sanitizer::sanitize($request->input('observacao')) . "\n";
            }

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Impressão - ' . Sanitizer::sanitize($request->input('titulo')),
                'description' => $fullDescription,
                'area_id' => $area->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'impressao',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_prevista') ? \Carbon\Carbon::parse($request->input('data_prevista')) : $slaData['due_at'],
                'last_status_change_at' => now(),
            ]);

            if ($request->hasFile('attachments')) {
                FileUploadHelper::processAttachments($ticket, $request->file('attachments'), $user->id);
            }

            TicketEventService::log($ticket, $user, 'created', null, 'open', [
                'titulo' => $request->input('titulo'),
                'tipo_impressao' => $request->input('tipo_impressao'),
                'quantidade' => $request->input('quantidade'),
            ]);

            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de Impressão criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}











