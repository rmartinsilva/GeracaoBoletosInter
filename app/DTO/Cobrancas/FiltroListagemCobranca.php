<?php

namespace App\DTO\Cobrancas;

use App\Enums\FiltroEmissaoCobranca;
use App\Enums\FiltroSituacaoCobranca;

class FiltroListagemCobranca
{
    public function __construct(
        public string $de,
        public string $ate,
        public ?string $busca,
        public ?FiltroSituacaoCobranca $situacao,
        public ?FiltroEmissaoCobranca $emissao,
        public string $hoje,
    ) {}
}
