<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketEvent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TicketEventService
{
    public static function log(
        Ticket $ticket,
        ?User $user,
        string $type,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?array $meta = null,
        ?\DateTimeInterface $occurredAt = null
    ): TicketEvent {
        return TicketEvent::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id ?? Auth::id(),
            'type' => $type,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'meta' => $meta,
            'occurred_at' => $occurredAt ?? now(),
        ]);
    }
}

