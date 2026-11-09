<?php

namespace App\Enums;

enum FiltroSituacaoCobranca: string
{
    case Aberto = 'aberto';
    case Pago = 'pago';
    case Cancelado = 'cancelado';
    case Vencido = 'vencido';

    /**
     * Rótulo exibido na interface.
     */
    public function rotulo(): string
    {
        return match ($this) {
            self::Aberto => 'Em aberto',
            self::Pago => 'Pago',
            self::Cancelado => 'Cancelado',
            self::Vencido => 'Vencido',
        };
    }
}
