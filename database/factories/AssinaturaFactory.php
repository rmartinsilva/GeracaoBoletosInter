<?php

namespace Database\Factories;

use App\Enums\Periodicidade;
use App\Enums\SituacaoAssinatura;
use App\Models\Assinatura;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assinatura>
 */
class AssinaturaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'descricao' => 'Mensalidade',
            'valor' => fake()->randomFloat(2, 50, 5000),
            'periodicidade' => Periodicidade::Mensal,
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
            'data_inicio' => '2031-01-10',
            'data_fim' => null,
            'situacao' => SituacaoAssinatura::Ativa,
        ];
    }

    /**
     * Assinatura com cobrança mensal.
     */
    public function mensal(): static
    {
        return $this->state(fn (array $attributes) => [
            'periodicidade' => Periodicidade::Mensal,
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
        ]);
    }

    /**
     * Assinatura com cobrança anual.
     */
    public function anual(): static
    {
        return $this->state(fn (array $attributes) => [
            'periodicidade' => Periodicidade::Anual,
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
        ]);
    }

    /**
     * Assinatura encerrada, com o histórico preservado.
     */
    public function encerrada(): static
    {
        return $this->state(fn (array $attributes) => [
            'situacao' => SituacaoAssinatura::Encerrada,
            'data_fim' => '2031-01-31',
        ]);
    }
}
