<?php

namespace App\DTO\Cobrancas;

class ResumoListagemCobranca
{
    public function __construct(
        public int $quantidade,
        public string $valor,
        public int $pagas,
        public string $valor_pago,
        public int $abertas,
        public string $valor_aberto,
        public int $vencidas,
        public int $canceladas,
        public int $falhas,
        public int $sem_boleto,
        public int $emitidas,
    ) {}
}
