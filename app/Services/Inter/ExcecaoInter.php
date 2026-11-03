<?php

namespace App\Services\Inter;

use App\Enums\Inter\AmbienteInter;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;
use Throwable;

class ExcecaoInter extends RuntimeException
{
    /**
     * @param  array<string, mixed>|null  $corpo
     * @param  list<array<string, mixed>>  $violacoes
     */
    public function __construct(
        string $mensagem,
        public readonly int $statusHttp = 0,
        public readonly ?array $corpo = null,
        public readonly array $violacoes = [],
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensagem, 0, $anterior);
    }

    public static function conexao(ConnectionException $excecao, AmbienteInter $ambiente): self
    {
        $mensagem = 'Não foi possível conectar à API do Banco Inter.';

        if (str_contains($excecao->getMessage(), 'unknown ca')) {
            $mensagem .= $ambiente === AmbienteInter::Sandbox
                ? ' O sandbox recusou o certificado. O arquivo baixado no Internet Banking é da produção; para usá-lo, defina INTER_AMBIENTE=producao. O sandbox usa outra integração, com outro certificado.'
                : ' O servidor recusou o certificado. Confira se o arquivo é do ambiente definido em INTER_AMBIENTE.';
        }

        return new self($mensagem, anterior: $excecao);
    }
}
