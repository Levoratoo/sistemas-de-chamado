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

class TicketDesenvolvimentoProdutoController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        $requestType = RequestType::where('slug', 'desenvol-produto')->first();
        return view('tickets.create-mockup', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'solicitacao_pre' => 'nullable|array',
            'solicitacao_pre.*' => 'string|in:gabarito,layout,mock-up,mock-up-impresso,puxada-cor,3d-site,prova-contratual,desenvol-produto',
            'produto' => 'required|string|max:255',
            'calculo_opcao' => 'nullable|string|max:255',
            'furo_fita' => 'nullable|string|max:255',
            'cor_alca' => 'nullable|string|max:255',
            'cores' => 'nullable|string|max:255',
            'embalagem_transporte' => 'nullable|string|max:255',
            'data_prevista' => 'nullable|date|after_or_equal:today',
            'observacao' => 'nullable|string|max:5000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
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
                    'name' => 'Desenvolvimento de Produto',
                    'description' => 'Categoria para solicitações de Desenvolvimento de Produto',
                    'active' => true,
                ]);
            }

            $slaData = $slaService->calculateSlaForTicket('desenvol-produto', 'medium', $category->id);

            $servicos = [
                'gabarito' => 'Gabarito',
                'layout' => 'Layout',
                'mock-up' => 'Mock up',
                'mock-up-impresso' => 'Mock up Impresso',
                'puxada-cor' => 'Puxada de Cor',
                '3d-site' => '3D/Site',
                'prova-contratual' => 'Prova Contratual',
                'desenvol-produto' => 'Desenvol. Produto',
            ];
            $solicitacoesPre = [];
            if ($request->input('solicitacao_pre')) {
                $solicitacoesPre = array_map(function($slug) use ($servicos) {
                    return $servicos[$slug] ?? ucfirst(str_replace('-', ' ', $slug));
                }, $request->input('solicitacao_pre', []));
            }

            $fullDescription = "Título: " . Sanitizer::sanitize($request->input('titulo')) . "\n\n";
            if (!empty($solicitacoesPre)) {
                $fullDescription .= "Solicitação Pré: " . implode(', ', $solicitacoesPre) . "\n\n";
            }
            $fullDescription .= "Produto: " . Sanitizer::sanitize($request->input('produto')) . "\n\n";
            if ($request->input('calculo_opcao')) $fullDescription .= "Cálculo / Opção: " . Sanitizer::sanitize($request->input('calculo_opcao')) . "\n\n";
            if ($request->input('furo_fita')) $fullDescription .= "Furo de Fita: " . Sanitizer::sanitize($request->input('furo_fita')) . "\n\n";
            if ($request->input('cor_alca')) $fullDescription .= "Cor da Alça: " . Sanitizer::sanitize($request->input('cor_alca')) . "\n\n";
            if ($request->input('cores')) $fullDescription .= "Cores: " . Sanitizer::sanitize($request->input('cores')) . "\n\n";
            if ($request->input('embalagem_transporte')) $fullDescription .= "Embalagem para Transporte: " . Sanitizer::sanitize($request->input('embalagem_transporte')) . "\n\n";
            if ($request->input('data_prevista')) $fullDescription .= "Data Prevista: " . \Carbon\Carbon::parse($request->input('data_prevista'))->format('d/m/Y') . "\n\n";
            if ($request->input('observacao')) $fullDescription .= "Observação: " . Sanitizer::sanitize($request->input('observacao')) . "\n";

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => 'Desenvolvimento de Produto - ' . Sanitizer::sanitize($request->input('titulo')),
                'description' => $fullDescription,
                'area_id' => $area->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'desenvol-produto',
                'respond_by' => $slaData['respond_by'],
                'due_at' => $request->input('data_prevista') ? \Carbon\Carbon::parse($request->input('data_prevista')) : $slaData['due_at'],
                'last_status_change_at' => now(),
            ]);

            if ($request->hasFile('attachments')) {
                FileUploadHelper::processAttachments($ticket, $request->file('attachments'), $user->id);
            }

            TicketEventService::log($ticket, $user, 'created', null, 'open', [
                'titulo' => $request->input('titulo'),
                'solicitacao_pre' => !empty($solicitacoesPre) ? implode(', ', $solicitacoesPre) : '',
                'produto' => $request->input('produto'),
            ]);

            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de Desenvolvimento de Produto criada com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}









