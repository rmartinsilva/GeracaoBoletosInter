<?php

namespace App\Http\Requests\Painel;

use App\Enums\Periodicidade;
use App\Support\ValorMonetario;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssinaturaRequest extends FormRequest
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
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|decimal:0,2|gt:0|max:99999999.99',
            'periodicidade' => ['required', Rule::enum(Periodicidade::class)],
            'dia_vencimento' => ['required_unless:periodicidade,avulso', 'nullable', 'integer', 'between:1,31'],
            'dia_geracao' => ['required_unless:periodicidade,avulso', 'nullable', 'integer', 'between:1,31'],
            'data_inicio' => 'required|date',
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
            'periodicidade.required' => 'A periodicidade é obrigatória.',
            'periodicidade.enum' => 'Escolha uma periodicidade válida: mensal, anual ou avulso.',
            'dia_vencimento.required_unless' => 'Informe o dia de vencimento.',
            'dia_vencimento.integer' => 'O dia de vencimento deve ser um número de 1 a 31.',
            'dia_vencimento.between' => 'O dia de vencimento deve ser um número de 1 a 31.',
            'dia_geracao.required_unless' => 'Informe o dia de geração da cobrança.',
            'dia_geracao.integer' => 'O dia de geração deve ser um número de 1 a 31.',
            'dia_geracao.between' => 'O dia de geração deve ser um número de 1 a 31.',
            'data_inicio.required' => 'A data de início é obrigatória.',
            'data_inicio.date' => 'Informe uma data de início válida.',
        ];
    }

    /**
     * Converte o valor digitado no formato brasileiro antes da validação.
     */
    protected function prepareForValidation(): void
    {
        $dia = $this->input('dia_vencimento');
        $geracao = $this->input('dia_geracao');

        $this->merge([
            'valor' => ValorMonetario::normalizar($this->input('valor')),
            'dia_vencimento' => $dia === '' || $dia === null ? null : $dia,
            'dia_geracao' => $geracao === '' || $geracao === null ? null : $geracao,
        ]);
    }
}
