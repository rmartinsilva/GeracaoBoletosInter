<?php

namespace App\Services\Inter;

class ValorInter
{
    public static function data(string $data, string $campo): string
    {
        $data = trim($data);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            throw new ExcecaoInter("A {$campo} deve estar no formato AAAA-MM-DD.");
        }

        [$ano, $mes, $dia] = array_map(intval(...), explode('-', $data));

        if (! checkdate($mes, $dia, $ano)) {
            throw new ExcecaoInter("A {$campo} informada não existe.");
        }

        return $data;
    }

    public static function dataHora(string $dataHora, string $campo): string
    {
        $dataHora = trim($dataHora);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2}(\.\d{1,3})?)?(Z|[+-]\d{2}:\d{2})$/', $dataHora)) {
            throw new ExcecaoInter("A {$campo} deve estar no formato ISO, por exemplo 2026-11-02T00:00:00Z.");
        }

        return $dataHora;
    }

    public static function dinheiro(float|int|string $valor, string $campo, bool $faixaCobranca = false): float
    {
        if (is_string($valor)) {
            $valor = trim($valor);
        }

        if (! is_numeric($valor)) {
            throw new ExcecaoInter("O {$campo} informado é inválido. Use o formato decimal, por exemplo 1500.00.");
        }

        $normalizado = round((float) $valor, 2);
        $centavos = (int) round($normalizado * 100);

        if ($faixaCobranca && ($centavos < 250 || $centavos > 9_999_999_999)) {
            throw new ExcecaoInter('O valor nominal deve estar entre 2,50 e 99.999.999,99.');
        }

        if (! $faixaCobranca && $centavos < 0) {
            throw new ExcecaoInter("O {$campo} não pode ser negativo.");
        }

        return $normalizado;
    }

    public static function digitos(string $valor): string
    {
        return preg_replace('/\D+/', '', $valor) ?? '';
    }

    public static function textoObrigatorio(string $valor, string $mensagem): string
    {
        $texto = trim($valor);

        if ($texto === '') {
            throw new ExcecaoInter($mensagem);
        }

        return $texto;
    }

    public static function textoOpcional(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $texto = trim($valor);

        return $texto === '' ? null : $texto;
    }

    public static function codigo(string $codigo): string
    {
        $codigo = trim($codigo);

        if (! preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{8,12}$/', $codigo)) {
            throw new ExcecaoInter('O código da cobrança deve ser o identificador retornado pelo Banco Inter.');
        }

        return strtolower($codigo);
    }
}
