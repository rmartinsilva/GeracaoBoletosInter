<?php

namespace App\Enums\Inter;

enum AmbienteInter: string
{
    case Sandbox = 'sandbox';
    case Producao = 'producao';

    public function rotulo(): string
    {
        return match ($this) {
            self::Sandbox => 'Sandbox',
            self::Producao => 'Produção',
        };
    }
}
