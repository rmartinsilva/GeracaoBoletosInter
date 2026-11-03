<?php

namespace App\Http\Requests\Painel;

use App\Enums\SituacaoAssinatura;
use App\Models\Assinatura;
use App\Support\ValorMonetario;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssinaturaRequest extends FormRequest
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
        $assinatura = Assinatura::query()->find($this->route('assinatura'));
        $inicio = $assinatura?->data_inicio?->toDateString() ?? '1900-01-01';

        return [
            'descricao' => 'required|string|max:255',
            'valor' => 'required|numeric|decimal:0,2|gt:0|max:99999999.99',
            'dia_vencimento' => ['required', 'integer', 'between:1,31'],
            'dia_geracao' => ['required', 'integer', 'between:1,31'],
            'situacao' => ['required', Rule::enum(SituacaoAssinatura::class)],
            'data_fim' => ['nullable', 'date', 'required_if:situacao,encerrada', 'after_or_equal:'.$inicio],
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
            'dia_vencimento.required' => 'Informe o dia de vencimento.',
            'dia_vencimento.integer' => 'O dia de vencimento deve ser um número de 1 a 31.',
            'dia_vencimento.between' => 'O dia de vencimento deve ser um número de 1 a 31.',
            'dia_geracao.required' => 'Informe o dia de geração da cobrança.',
            'dia_geracao.integer' => 'O dia de geração deve ser um número de 1 a 31.',
            'dia_geracao.between' => 'O dia de geração deve ser um número de 1 a 31.',
            'situacao.required' => 'A situação é obrigatória.',
            'situacao.enum' => 'Escolha uma situação válida: ativa ou encerrada.',
            'data_fim.required_if' => 'Informe a data de encerramento.',
            'data_fim.date' => 'Informe uma data de encerramento válida.',
            'data_fim.after_or_equal' => 'A data de encerramento não pode ser anterior ao início da assinatura.',
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
