<?php

namespace App\Enums;

enum SituacaoAssinatura: string
{
    case Ativa = 'ativa';
    case Encerrada = 'encerrada';

    /**
     * Rótulo exibido na interface.
     */
    public function rotulo(): string
    {
        return match ($this) {
            self::Ativa => 'Ativa',
            self::Encerrada => 'Encerrada',
        };
    }
}
