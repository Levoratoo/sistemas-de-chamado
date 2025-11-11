<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class ReturnToQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');
        
        if (!$ticket) {
            return false;
        }

        // Apenas quem pode trabalhar no ticket pode devolver para fila
        return $this->user()->can('work', $ticket);
    }

    public function rules(): array
    {
        return [
            'reason' => 'nullable|string|max:1000',
        ];
    }
}











