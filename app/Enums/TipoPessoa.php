<?php

namespace App\Enums;

enum TipoPessoa: string
{
    case Fisica = 'fisica';
    case Juridica = 'juridica';

    public function rotulo(): string
    {
        return match ($this) {
            self::Fisica => 'Pessoa física',
            self::Juridica => 'Pessoa jurídica',
        };
    }
}
