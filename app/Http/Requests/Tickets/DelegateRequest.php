<?php

namespace App\Http\Requests\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Http\FormRequest;

class DelegateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');
        $assigneeId = (int) $this->input('assignee_id');
        
        if (!$ticket instanceof Ticket) {
            return false;
        }

        // Se o ticket já tem dono, apenas admin/gestor pode delegar
        if ($ticket->assignee_id && !auth()->user()->isAdmin() && !auth()->user()->isGestor()) {
            return false;
        }

        // Valida se o novo assignee pode trabalhar no ticket
        $newAssignee = User::find($assigneeId);
        if (!$newAssignee) {
            return false;
        }

        return TicketService::canWorkOnTicket($newAssignee, $ticket);
    }

    public function rules(): array
    {
        return [
            'assignee_id' => 'required|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'assignee_id.required' => 'Selecione um usuário para delegar.',
            'assignee_id.exists' => 'Usuário selecionado não existe.',
        ];
    }
}











