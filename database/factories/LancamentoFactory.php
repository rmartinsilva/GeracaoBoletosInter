<?php

namespace Database\Factories;

use App\Enums\SituacaoLancamento;
use App\Models\Assinatura;
use App\Models\Lancamento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lancamento>
 */
class LancamentoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assinatura_id' => Assinatura::factory(),
            'competencia' => '2031-05-01',
            'valor' => 150,
            'vencimento' => '2031-05-10',
            'situacao' => SituacaoLancamento::Aberto,
            'pago_em' => null,
        ];
    }

    /**
     * Lançamento já pago.
     */
    public function pago(): static
    {
        return $this->state(fn (array $attributes) => [
            'situacao' => SituacaoLancamento::Pago,
            'pago_em' => '2031-05-10 03:00:00',
        ]);
    }
}
