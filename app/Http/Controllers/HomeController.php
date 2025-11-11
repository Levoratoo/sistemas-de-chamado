<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Página inicial - visão geral simples e limpa
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Métricas básicas e essenciais
        $myTickets = $user->requestedTickets()->count();
        $assignedTickets = $user->assignedTickets()->count();
        
        // Chamados urgentes (próximos do vencimento)
        $urgentTickets = Ticket::where('due_at', '>', now())
            ->where('due_at', '<=', now()->addHours(4))
            ->where('status', '!=', Ticket::STATUS_FINALIZED)
            ->when(!$user->canManageTickets(), function ($query) use ($user) {
                return $query->where('requester_id', $user->id)
                    ->orWhere('assignee_id', $user->id);
            })
            ->when($user->isAtendente() && !$user->isAdmin() && !$user->isGestor(), function ($query) use ($user) {
                return $query->where('area_id', $user->team_id);
            })
            ->count();
        
        // Chamados vencidos
        // Um ticket está vencido apenas se passaram mais de 7 dias desde a criação
        $sevenDaysAgo = now()->subDays(7);
        $overdueTickets = Ticket::where('status', '!=', Ticket::STATUS_FINALIZED)
            ->where('created_at', '<', $sevenDaysAgo)
            ->when(!$user->canManageTickets(), function ($query) use ($user) {
                return $query->where('requester_id', $user->id)
                    ->orWhere('assignee_id', $user->id);
            })
            ->when($user->isAtendente() && !$user->isAdmin() && !$user->isGestor(), function ($query) use ($user) {
                $areaIds = $user->groupsAreasIds();
                return $query->whereIn('area_id', !empty($areaIds) ? $areaIds : [-1]);
            })
            ->count();
        
        // Chamados recentes (últimos 5)
        $recentTickets = Ticket::with(['category', 'requester', 'assignee', 'area'])
            ->when(!$user->canManageTickets(), function ($query) use ($user) {
                return $query->where('requester_id', $user->id)
                    ->orWhere('assignee_id', $user->id);
            })
            ->when($user->isAtendente() && !$user->isAdmin() && !$user->isGestor(), function ($query) use ($user) {
                return $query->where(function ($inner) use ($user) {
                    $inner->where('area_id', $user->team_id)
                        ->orWhere('assignee_id', $user->id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Ações rápidas baseadas no perfil do usuário
        $quickActions = $this->getQuickActions($user);
        
        return view('home.index', compact(
            'myTickets', 
            'assignedTickets', 
            'urgentTickets', 
            'overdueTickets', 
            'recentTickets',
            'quickActions'
        ));
    }
    
    /**
     * Define ações rápidas baseadas no perfil do usuário
     */
    private function getQuickActions($user)
    {
        $actions = [];

        // Ação rápida sempre para página de seleção de área
        $actions[] = [
            'title' => 'Novo Chamado',
            'description' => 'Criar um novo chamado',
            'icon' => 'plus',
            'route' => 'request-areas.index',
            'color' => 'blue'
        ];

        // Atendentes e gestores podem ver a fila
        if ($user->isAtendente() || $user->isGestor() || $user->isAdmin()) {
            $actions[] = [
                'title' => 'Fila de Chamados',
                'description' => 'Ver chamados disponíveis',
                'icon' => 'queue-list',
                'route' => 'queue.index',
                'color' => 'green'
            ];
        }

        // Todos podem ver seus chamados
        $actions[] = [
            'title' => 'Meus Chamados',
            'description' => 'Chamados que você criou',
            'icon' => 'ticket',
            'route' => 'tickets.index',
            'color' => 'purple'
        ];

        // Gestores e admins podem ver dashboard executivo
        if ($user->isGestor() || $user->isAdmin()) {
            $actions[] = [
                'title' => 'Dashboard Executivo',
                'description' => 'Relatórios e métricas',
                'icon' => 'chart-bar',
                'route' => 'dashboard',
                'color' => 'indigo'
            ];
        }

        // Usuários podem ver avaliações pendentes
        if ($user->requestedTickets()->where('status', Ticket::STATUS_FINALIZED)->exists()) {
            $actions[] = [
                'title' => 'Avaliações Pendentes',
                'description' => 'Avaliar chamados finalizados',
                'icon' => 'star',
                'route' => 'evaluations.pending',
                'color' => 'yellow'
            ];
        }

        return $actions;
    }
}