<?php

namespace App\Http\Requests\Painel;

use App\Services\DataHoraService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarPagamentoRequest extends FormRequest
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
            'data_pagamento' => ['required', 'date', $this->naoFutura()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'data_pagamento.required' => 'Informe a data do pagamento.',
            'data_pagamento.date' => 'Informe uma data de pagamento válida.',
        ];
    }

    /**
     * A data de "hoje" é a de Brasília, não a do UTC.
     */
    private function naoFutura(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $hoje = app(DataHoraService::class)->agora()->toDateString();

            if (is_string($value) && $value > $hoje) {
                $fail('A data do pagamento não pode ser futura.');
            }
        };
    }
}
