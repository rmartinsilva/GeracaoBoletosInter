<?php

namespace App\Http\Requests\Painel;

use App\Services\DataHoraService;
use App\Support\ValorMonetario;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCobrancaAvulsaRequest extends FormRequest
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
        $hoje = app(DataHoraService::class)->agora()->toDateString();

        return [
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|decimal:0,2|gt:0|max:99999999.99',
            'vencimento' => ['required', 'date', 'after_or_equal:'.$hoje],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição deve ter no máximo 255 caracteres.',
            'valor.required' => 'O valor é obrigatório.',
            'valor.numeric' => 'Informe um valor válido, por exemplo 1.500,00.',
            'valor.decimal' => 'O valor deve ter no máximo duas casas decimais.',
            'valor.gt' => 'O valor deve ser maior que zero.',
            'valor.max' => 'O valor deve ser menor que R$ 100.000.000,00.',
            'vencimento.required' => 'Informe a data de vencimento.',
            'vencimento.date' => 'Informe uma data de vencimento válida.',
            'vencimento.after_or_equal' => 'O vencimento não pode ser anterior a hoje.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'valor' => ValorMonetario::normalizar($this->input('valor')),
        ]);
    }
}
