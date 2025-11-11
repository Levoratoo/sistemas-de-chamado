<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;

class TicketService
{
    /**
     * Determine if a user is eligible to work on a given ticket.
     */
    public static function canWorkOnTicket(User $candidate, Ticket $ticket): bool
    {
        if ($ticket->assignee_id === $candidate->id) {
            return true;
        }

        if ($candidate->isAdmin()) {
            return true;
        }

        $areaIds = $candidate->groupsAreasIds();

        if ($candidate->isGestor() && in_array($ticket->area_id, $areaIds, true)) {
            return true;
        }

        if ($candidate->isAtendente() && in_array($ticket->area_id, $areaIds, true)) {
            return true;
        }

        // Permite que usuários comuns com a mesma área também possam ser delegados
        if ($candidate->isUsuario() && in_array($ticket->area_id, $areaIds, true)) {
            return true;
        }

        return false;
    }

    /**
     * Return a collection of users that can be assigned to the ticket.
     */
    public static function eligibleAssignees(Ticket $ticket, User $actor): Collection
    {
        $users = User::query()
            ->with(['role', 'areas'])
            ->select('id', 'name', 'email', 'role_id')
            ->orderBy('name')
            ->get();

        return $users->filter(function (User $candidate) use ($ticket) {
            // Limpa o cache de áreas para este candidato
            $candidate->areaCache = null;
            return self::canWorkOnTicket($candidate, $ticket);
        })->values();
    }
}
