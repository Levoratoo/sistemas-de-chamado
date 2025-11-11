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

class TicketEmployeeController extends Controller
{
    public function create(Request $request)
    {
        $areas = Area::where('active', true)->get();
        $categories = Category::where('active', true)->get();
        
        // Buscar o tipo de solicitação específico
        $requestType = RequestType::where('slug', 'novo-colaborador')->first();
        
        return view('tickets.create-employee', compact('areas', 'categories', 'requestType'));
    }

    public function store(Request $request, SlaCalculationService $slaService, NotificationService $notificationService)
    {
        $request->validate([
            'perfil' => 'required|in:contratacao,realocacao',
            'nome_completo' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'cargo_funcao' => 'required|string|max:255',
            'empresa' => 'required|string|max:255',
            'data_inicio' => 'required|date|after:today',
            'acessos_liberacoes' => 'nullable|array',
            'acessos_liberacoes.*' => 'in:email,teams,acesso-remoto,whatsapp,youtube-streaming,skype',
            'acessos_metrics' => 'nullable|string',
            'acessos_wk' => 'nullable|string',
            'acesso_pastas_rede' => 'nullable|string',
            'outras_necessidades' => 'nullable|string',
        ]);

        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $user, $slaService, $notificationService) {
            // Buscar área TI
            $tiArea = Area::where('name', 'LIKE', '%TI%')->first();
            if (!$tiArea) {
                $tiArea = Area::first(); // Fallback
            }

            // Buscar categoria padrão
            $category = Category::where('active', true)->first();
            if (!$category) {
                $category = Category::create([
                    'name' => 'Novo Colaborador',
                    'description' => 'Categoria para novo colaborador',
                    'active' => true,
                ]);
            }

            // Calcular SLA
            $slaData = $slaService->calculateSlaForTicket('novo-colaborador', 'medium', $category->id);

            // Converter slug do cargo para nome legível
            $cargoSlug = $request->input('cargo_funcao');
            $cargoNome = $cargoSlug ? strtoupper(str_replace('-', ' ', $cargoSlug)) : '';

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => Sanitizer::sanitize($request->input('nome_completo')),
                'description' => $cargoNome ? $cargoNome . "\n\n" . Sanitizer::sanitizeWithFormatting($request->input('outras_necessidades') ?? '') : Sanitizer::sanitizeWithFormatting($request->input('outras_necessidades') ?? ''),
                'area_id' => $tiArea->id,
                'category_id' => $category->id,
                'requester_id' => $user->id,
                'status' => 'open',
                'priority' => 'medium',
                'request_type' => 'novo-colaborador',
                'respond_by' => $slaData['respond_by'],
                'due_at' => \Carbon\Carbon::parse($request->input('data_inicio')),
                'last_status_change_at' => now(),
            ]);

            // Log do evento
            TicketEventService::log($ticket, $user, 'created', null, null, [
                'perfil' => $request->input('perfil'),
                'departamento' => $request->input('departamento'),
                'empresa' => $request->input('empresa'),
                'data_inicio' => $request->input('data_inicio'),
                'acessos_liberacoes' => $request->input('acessos_liberacoes'),
                'acessos_metrics' => $request->input('acessos_metrics'),
                'acessos_wk' => $request->input('acessos_wk'),
                'acesso_pastas_rede' => $request->input('acesso_pastas_rede'),
                'outras_necessidades' => $request->input('outras_necessidades'),
            ]);

            // Notificar área TI
            $notificationService->notifyTicketCreated($ticket);

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Chamado de Novo Colaborador criado com sucesso!');
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }
}