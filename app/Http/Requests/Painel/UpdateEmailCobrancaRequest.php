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
            'email_falha' => 'nullable|email|max:255',
            'email_copia' => 'nullable|email|max:255',
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
            'email_falha.email' => 'Informe um e-mail válido para o aviso de falha.',
            'email_falha.max' => 'O e-mail de falha deve ter no máximo 255 caracteres.',
            'email_copia.email' => 'Informe um e-mail válido para a cópia oculta.',
            'email_copia.max' => 'A cópia oculta deve ter no máximo 255 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = mb_strtolower(trim((string) $this->input('email_falha', '')));
        $copia = mb_strtolower(trim((string) $this->input('email_copia', '')));

        $this->merge([
            'email_falha' => $email === '' ? null : $email,
            'email_copia' => $copia === '' ? null : $copia,
        ]);
    }
}
