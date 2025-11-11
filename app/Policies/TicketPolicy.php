<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        if ($ticket->requester_id === $user->id) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($ticket->assignee_id === $user->id) {
            return true;
        }

        if ($user->isGestor() && in_array($ticket->area_id, $user->groupsAreasIds(), true)) {
            return true;
        }

        return false;
    }

    public function work(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isGestor() && in_array($ticket->area_id, $user->groupsAreasIds(), true)) {
            return true;
        }

        if ($ticket->assignee_id === $user->id) {
            return true;
        }

        return $user->isAtendente()
            && $ticket->assignee_id === null
            && in_array($ticket->area_id, $user->groupsAreasIds(), true);
    }

    public function downloadAttachment(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }
}
