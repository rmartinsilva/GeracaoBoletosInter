<?php

namespace App\DTO\Inter;

use App\Services\Inter\ExcecaoInter;
use App\Services\Inter\PayloadInter;
use App\Services\Inter\ValorInter;

class EditarCobrancaDTO
{
    public readonly ?string $dataVencimento;

    public readonly ?float $valorNominal;

    public function __construct(
        ?string $dataVencimento = null,
        float|int|string|null $valorNominal = null,
    ) {
        $this->dataVencimento = $dataVencimento === null || trim($dataVencimento) === ''
            ? null
            : ValorInter::data($dataVencimento, 'data de vencimento');
        $this->valorNominal = $valorNominal === null || $valorNominal === ''
            ? null
            : ValorInter::dinheiro($valorNominal, 'valor nominal', faixaCobranca: true);

        if ($this->dataVencimento === null && $this->valorNominal === null) {
            throw new ExcecaoInter('Informe a nova data de vencimento ou o novo valor nominal.');
        }
    }

    /**
     * @return array<string, float|string>
     */
    public function paraPayload(): array
    {
        return PayloadInter::semNulos([
            'dataVencimento' => $this->dataVencimento,
            'valorNominal' => $this->valorNominal,
        ]);
    }
}
