<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class AttachmentFromShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');
        
        if (!$ticket) {
            return false;
        }

        return $this->user()->can('work', $ticket);
    }

    public function rules(): array
    {
        return [
            'attachments.*' => 'required|file|max:10240|mimetypes:application/pdf,image/png,image/jpeg,image/jpg,image/gif,text/plain,application/zip,application/x-zip-compressed,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.*.file' => 'O arquivo selecionado é inválido.',
            'attachments.*.max' => 'O arquivo não pode ter mais de 10MB.',
            'attachments.*.mimetypes' => 'Tipo de arquivo não permitido.',
        ];
    }
}











