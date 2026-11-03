<?php

namespace App\Services\Inter;

class PayloadInter
{
    /**
     * Remove nulos e listas vazias, preservando zero e falso.
     *
     * @param  array<mixed>  $dados
     * @return array<mixed>
     */
    public static function semNulos(array $dados): array
    {
        if (array_is_list($dados)) {
            $itens = [];

            foreach ($dados as $valor) {
                if ($valor === null) {
                    continue;
                }

                $itens[] = is_array($valor) ? self::semNulos($valor) : $valor;
            }

            return $itens;
        }

        $resultado = [];

        foreach ($dados as $chave => $valor) {
            if ($valor === null) {
                continue;
            }

            if (is_array($valor)) {
                $valor = self::semNulos($valor);

                if ($valor === []) {
                    continue;
                }
            }

            $resultado[$chave] = $valor;
        }

        return $resultado;
    }
}
