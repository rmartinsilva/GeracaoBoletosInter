<?php

namespace App\Enums\Inter;

enum OrdenarCobrancasPor: string
{
    case PessoaPagadora = 'PESSOA_PAGADORA';
    case TipoCobranca = 'TIPO_COBRANCA';
    case CodigoCobranca = 'CODIGO_COBRANCA';
    case Identificador = 'IDENTIFICADOR';
    case DataEmissao = 'DATA_EMISSAO';
    case DataVencimento = 'DATA_VENCIMENTO';
    case Valor = 'VALOR';
    case Status = 'STATUS';
}
