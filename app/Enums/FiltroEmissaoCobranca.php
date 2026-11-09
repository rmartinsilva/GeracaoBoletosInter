<?php

namespace App\Enums;

enum FiltroEmissaoCobranca: string
{
    case Emitida = 'emitida';
    case SemBoleto = 'sem_boleto';
    case Falha = 'falha';

    /**
     * Rótulo exibido na interface.
     */
    public function rotulo(): string
    {
        return match ($this) {
            self::Emitida => 'Emitida',
            self::SemBoleto => 'Sem boleto',
            self::Falha => 'Com falha',
        };
    }
}
