<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:3', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Informe a mensagem para o solicitante.',
            'message.min' => 'A mensagem precisa ter ao menos :min caracteres.',
            'message.max' => 'A mensagem pode ter no maximo :max caracteres.',
        ];
    }
}

