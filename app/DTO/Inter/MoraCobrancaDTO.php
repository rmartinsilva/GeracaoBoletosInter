<?php

namespace App\DTO\Inter;

use App\Enums\Inter\CodigoMora;
use App\Services\Inter\ExcecaoInter;
use App\Services\Inter\ValorInter;

class MoraCobrancaDTO
{
    public readonly ?float $taxaOuValor;

    public function __construct(
        public readonly CodigoMora $codigo,
        float|int|string|null $taxaOuValor = null,
    ) {
        if ($this->codigo === CodigoMora::Isento) {
            $this->taxaOuValor = null;

            return;
        }

        if ($taxaOuValor === null || $taxaOuValor === '') {
            throw new ExcecaoInter('Informe a taxa ou o valor da mora.');
        }

        $this->taxaOuValor = ValorInter::dinheiro($taxaOuValor, 'mora');

        if ($this->taxaOuValor <= 0) {
            throw new ExcecaoInter('A mora deve ser maior que zero.');
        }
    }

    /**
     * @return array{codigo: string, taxa?: float, valor?: float}
     */
    public function paraPayload(): array
    {
        $payload = ['codigo' => $this->codigo->value];

        if ($this->taxaOuValor === null) {
            return $payload;
        }

        if ($this->codigo === CodigoMora::TaxaMensal) {
            $payload['taxa'] = $this->taxaOuValor;
        } else {
            $payload['valor'] = $this->taxaOuValor;
        }

        return $payload;
    }
}
