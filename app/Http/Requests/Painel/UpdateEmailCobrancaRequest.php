<?php

namespace App\Http\Requests\Painel;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailCobrancaRequest extends FormRequest
{
    /**
     * A permissão é verificada no controller, com a lista de permissões da sessão.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'corpo_email' => 'required|string|max:5000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'corpo_email.required' => 'O texto do e-mail é obrigatório.',
            'corpo_email.max' => 'O texto do e-mail deve ter no máximo 5000 caracteres.',
        ];
    }
}
