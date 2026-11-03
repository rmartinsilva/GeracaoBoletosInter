<?php

namespace App\Enums;

enum Periodicidade: string
{
    case Mensal = 'mensal';
    case Anual = 'anual';

    /**
     * Rótulo exibido na interface.
     */
    public function rotulo(): string
    {
        return match ($this) {
            self::Mensal => 'Mensal',
            self::Anual => 'Anual',
        };
    }
}
