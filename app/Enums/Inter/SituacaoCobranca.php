<?php

namespace App\Enums\Inter;

enum SituacaoCobranca: string
{
    case Recebido = 'RECEBIDO';
    case AReceber = 'A_RECEBER';
    case MarcadoRecebido = 'MARCADO_RECEBIDO';
    case Atrasado = 'ATRASADO';
    case Cancelado = 'CANCELADO';
    case Expirado = 'EXPIRADO';
    case FalhaEmissao = 'FALHA_EMISSAO';
    case EmProcessamento = 'EM_PROCESSAMENTO';
    case Protesto = 'PROTESTO';

    public function rotulo(): string
    {
        return match ($this) {
            self::Recebido => 'Recebido',
            self::AReceber => 'A receber',
            self::MarcadoRecebido => 'Marcado como recebido',
            self::Atrasado => 'Atrasado',
            self::Cancelado => 'Cancelado',
            self::Expirado => 'Expirado',
            self::FalhaEmissao => 'Falha na emissão',
            self::EmProcessamento => 'Em processamento',
            self::Protesto => 'Protesto',
        };
    }
}
