<?php

namespace App\Enums\Inter;

enum FiltrarDataPor: string
{
    case Vencimento = 'VENCIMENTO';
    case Emissao = 'EMISSAO';
    case Pagamento = 'PAGAMENTO';
}
