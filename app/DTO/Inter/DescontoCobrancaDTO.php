<?php

namespace App\DTO\Inter;

use App\Enums\Inter\CodigoDesconto;
use App\Services\Inter\ExcecaoInter;
use App\Services\Inter\ValorInter;

class DescontoCobrancaDTO
{
    public readonly float $taxaOuValor;

    public function __construct(
        public readonly CodigoDesconto $codigo,
        public readonly int $quantidadeDias,
        float|int|string $taxaOuValor,
    ) {
        if ($this->quantidadeDias < 0 || $this->quantidadeDias > 60) {
            throw new ExcecaoInter('A quantidade de dias do desconto deve estar entre 0 e 60.');
        }

        $this->taxaOuValor = ValorInter::dinheiro($taxaOuValor, 'desconto');

        if ($this->taxaOuValor <= 0) {
            throw new ExcecaoInter('O desconto deve ser maior que zero.');
        }
    }

    /**
     * @return array{codigo: string, quantidadeDias: int, taxa?: float, valor?: float}
     */
    public function paraPayload(): array
    {
        $payload = [
            'codigo' => $this->codigo->value,
            'quantidadeDias' => $this->quantidadeDias,
        ];

        if ($this->codigo === CodigoDesconto::PercentualDataInformada) {
            $payload['taxa'] = $this->taxaOuValor;
        } else {
            $payload['valor'] = $this->taxaOuValor;
        }

        return $payload;
    }
}
