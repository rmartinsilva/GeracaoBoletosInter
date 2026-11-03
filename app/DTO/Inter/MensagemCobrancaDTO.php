<?php

namespace App\DTO\Inter;

use App\Services\Inter\PayloadInter;
use App\Services\Inter\ValorInter;

class MensagemCobrancaDTO
{
    public readonly ?string $linha1;

    public readonly ?string $linha2;

    public readonly ?string $linha3;

    public readonly ?string $linha4;

    public readonly ?string $linha5;

    public function __construct(
        ?string $linha1 = null,
        ?string $linha2 = null,
        ?string $linha3 = null,
        ?string $linha4 = null,
        ?string $linha5 = null,
    ) {
        $this->linha1 = ValorInter::textoOpcional($linha1);
        $this->linha2 = ValorInter::textoOpcional($linha2);
        $this->linha3 = ValorInter::textoOpcional($linha3);
        $this->linha4 = ValorInter::textoOpcional($linha4);
        $this->linha5 = ValorInter::textoOpcional($linha5);
    }

    /**
     * @return array<string, string>|null
     */
    public function paraPayload(): ?array
    {
        $payload = PayloadInter::semNulos([
            'linha1' => $this->linha1,
            'linha2' => $this->linha2,
            'linha3' => $this->linha3,
            'linha4' => $this->linha4,
            'linha5' => $this->linha5,
        ]);

        return $payload === [] ? null : $payload;
    }
}
