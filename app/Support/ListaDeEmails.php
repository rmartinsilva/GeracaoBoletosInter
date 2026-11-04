<?php

namespace App\Support;

/**
 * Separa, valida e junta os endereços que recebem o boleto.
 */
class ListaDeEmails
{
    public const LIMITE = 10;

    private const PADRAO = '/^[^@\s]+@[^@\s]+\.[^@\s]+$/';

    /**
     * @return list<string>
     */
    public static function separar(?string $texto): array
    {
        if ($texto === null || trim($texto) === '') {
            return [];
        }

        $partes = preg_split('/[\s,;]+/u', $texto) ?: [];
        $emails = [];

        foreach ($partes as $parte) {
            $email = mb_strtolower(trim($parte));

            if ($email === '' || in_array($email, $emails, true)) {
                continue;
            }

            $emails[] = $email;
        }

        return $emails;
    }

    public static function valido(string $email): bool
    {
        return mb_strlen($email) <= 255
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && preg_match(self::PADRAO, $email) === 1;
    }

    /**
     * Endereço principal e os adicionais, sem repetir e sem inválidos.
     *
     * @return list<string>
     */
    public static function juntar(?string $principal, ?string $adicionais): array
    {
        $lista = [];

        foreach (array_merge(self::separar($principal), self::separar($adicionais)) as $email) {
            if (! self::valido($email) || in_array($email, $lista, true)) {
                continue;
            }

            $lista[] = $email;
        }

        return $lista;
    }

    /**
     * Texto gravado no cliente: um e-mail por linha, sem o principal e sem repetidos.
     */
    public static function gravar(?string $texto, ?string $principal): ?string
    {
        $principalNormalizado = $principal === null ? null : mb_strtolower(trim($principal));
        $emails = [];

        foreach (self::separar($texto) as $email) {
            if ($email === $principalNormalizado || ! self::valido($email)) {
                continue;
            }

            $emails[] = $email;
        }

        return $emails === [] ? null : implode("\n", $emails);
    }
}
