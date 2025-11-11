<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinalizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'resolution_summary' => ['required', 'string', 'min:10', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'resolution_summary.required' => 'Descreva o que foi realizado para finalizar o chamado.',
            'resolution_summary.min' => 'O resumo precisa ter ao menos :min caracteres.',
            'resolution_summary.max' => 'O resumo pode ter no maximo :max caracteres.',
        ];
    }
}

