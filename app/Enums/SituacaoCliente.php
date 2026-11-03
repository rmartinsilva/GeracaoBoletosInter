<?php

namespace App\Enums;

enum SituacaoCliente: string
{
    case Ativo = 'ativo';
    case Inativo = 'inativo';

    /**
     * Rótulo exibido na interface.
     */
    public function rotulo(): string
    {
        return match ($this) {
            self::Ativo => 'Ativo',
            self::Inativo => 'Inativo',
        };
    }
}
