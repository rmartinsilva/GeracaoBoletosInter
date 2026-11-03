<?php

namespace App\Support;

class ValorMonetario
{
    /**
     * Converte um valor digitado no formato brasileiro ("R$ 1.500,00")
     * para o formato numérico ("1500.00").
     */
    public static function normalizar(mixed $valor): mixed
    {
        if (! is_string($valor)) {
            return $valor;
        }

        $normalizado = str_replace(['R$', ' ', "\u{00A0}"], '', trim($valor));

        if (str_contains($normalizado, ',')) {
            $normalizado = str_replace(['.', ','], ['', '.'], $normalizado);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $normalizado)) {
            $normalizado = str_replace('.', '', $normalizado);
        }

        return $normalizado;
    }
}
