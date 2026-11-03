<?php

namespace App\Enums\Inter;

enum TipoCobranca: string
{
    case Simples = 'SIMPLES';
    case Parcelado = 'PARCELADO';
    case Recorrente = 'RECORRENTE';
}
