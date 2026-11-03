<?php

namespace App\Enums;

enum Periodicidade: string
{
    case Mensal = 'mensal';
    case Anual = 'anual';
    case Avulso = 'avulso';

    /**
     * Rótulo exibido na interface.
     */
    public function rotulo(): string
    {
        return match ($this) {
            self::Mensal => 'Mensal',
            self::Anual => 'Anual',
            self::Avulso => 'Avulso',
        };
    }
}
