<?php

namespace App\Enums\Inter;

enum FormaRecebimento: string
{
    case Boleto = 'BOLETO';
    case Pix = 'PIX';
    case SemFormaPagamento = 'SEM_FORMA_PAGAMENTO';
}
