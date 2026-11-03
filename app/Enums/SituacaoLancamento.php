<?php

namespace App\Enums;

enum SituacaoLancamento: string
{
    case Aberto = 'aberto';
    case Pago = 'pago';
    case Cancelado = 'cancelado';

    /**
     * Rótulo exibido na interface.
     */
    public function rotulo(): string
    {
        return match ($this) {
            self::Aberto => 'Em aberto',
            self::Pago => 'Pago',
            self::Cancelado => 'Cancelado',
        };
    }
}
