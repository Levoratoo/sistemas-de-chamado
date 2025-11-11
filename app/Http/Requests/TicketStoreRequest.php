<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'area_id' => ['required', 'exists:areas,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'min:4', 'max:150'],
            'description' => ['required', 'string', 'min:10'],
            'attachments.*' => [
                'file',
                'max:10240',
                'mimetypes:application/pdf,image/png,image/jpeg,text/plain,application/zip',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'area_id.required' => 'Selecione o departamento responsavel.',
            'area_id.exists' => 'O departamento informado nao e valido.',
            'category_id.exists' => 'A categoria informada nao e valida.',
            'title.required' => 'Informe um titulo para o chamado.',
            'title.min' => 'O titulo precisa ter ao menos :min caracteres.',
            'title.max' => 'O titulo pode ter no maximo :max caracteres.',
            'description.required' => 'Descreva o problema encontrado.',
            'description.min' => 'A descricao precisa ter ao menos :min caracteres.',
            'attachments.*.file' => 'Cada anexo precisa ser um arquivo valido.',
            'attachments.*.max' => 'Cada anexo deve ter no maximo 10 MB.',
            'attachments.*.mimetypes' => 'Somente arquivos PDF, PNG, JPG/JPEG, TXT ou ZIP sao permitidos.',
        ];
    }
}
