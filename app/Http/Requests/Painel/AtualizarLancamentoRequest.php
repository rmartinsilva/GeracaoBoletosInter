<?php

namespace App\Http\Requests\Painel;

use App\Support\ValorMonetario;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AtualizarLancamentoRequest extends FormRequest
{
    /**
     * A permissão é verificada no controller, com a lista de permissões da sessão.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'valor' => 'required|numeric|decimal:0,2|gt:0|max:99999999.99',
            'vencimento' => 'required|date',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'valor.required' => 'O valor é obrigatório.',
            'valor.numeric' => 'Informe um valor válido, por exemplo 1.500,00.',
            'valor.decimal' => 'O valor deve ter no máximo duas casas decimais.',
            'valor.gt' => 'O valor deve ser maior que zero.',
            'valor.max' => 'O valor deve ser menor que R$ 100.000.000,00.',
            'vencimento.required' => 'Informe o vencimento.',
            'vencimento.date' => 'Informe um vencimento válido.',
        ];
    }

    /**
     * Converte o valor digitado no formato brasileiro antes da validação.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'valor' => ValorMonetario::normalizar($this->input('valor')),
        ]);
    }
}
