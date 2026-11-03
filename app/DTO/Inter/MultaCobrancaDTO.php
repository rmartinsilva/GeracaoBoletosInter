<?php

namespace App\DTO\Inter;

use App\Enums\Inter\CodigoMulta;
use App\Services\Inter\ExcecaoInter;
use App\Services\Inter\ValorInter;

class MultaCobrancaDTO
{
    public readonly float $taxaOuValor;

    public function __construct(
        public readonly CodigoMulta $codigo,
        float|int|string $taxaOuValor,
    ) {
        $this->taxaOuValor = ValorInter::dinheiro($taxaOuValor, 'multa');

        if ($this->taxaOuValor <= 0) {
            throw new ExcecaoInter('A multa deve ser maior que zero.');
        }
    }

    /**
     * @return array{codigo: string, taxa?: float, valor?: float}
     */
    public function paraPayload(): array
    {
        $payload = ['codigo' => $this->codigo->value];

        if ($this->codigo === CodigoMulta::Percentual) {
            $payload['taxa'] = $this->taxaOuValor;
        } else {
            $payload['valor'] = $this->taxaOuValor;
        }

        return $payload;
    }
}
