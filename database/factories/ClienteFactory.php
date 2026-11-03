<?php

namespace Database\Factories;

use App\Enums\TipoPessoa;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'nome_contato' => fake()->name(),
        ];
    }

    /**
     * Cliente com os dados que a API de cobrança exige do pagador.
     */
    public function comPagador(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_pessoa' => TipoPessoa::Fisica,
            'cpf_cnpj' => '12345678901',
            'cep' => '01310100',
            'endereco' => 'Avenida Paulista',
            'numero' => '1000',
            'bairro' => 'Bela Vista',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
        ]);
    }
}
