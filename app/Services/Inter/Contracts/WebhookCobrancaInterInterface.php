<?php

namespace App\Services\Inter\Contracts;

interface WebhookCobrancaInterInterface
{
    public function cadastrar(string $url): void;

    /**
     * @return array<string, mixed>
     */
    public function obter(): array;

    public function excluir(): void;

    /**
     * @return array<string, mixed>
     */
    public function listarCallbacks(
        string $dataHoraInicio,
        string $dataHoraFim,
        int $pagina = 0,
        int $tamanhoPagina = 20,
        ?string $codigoSolicitacao = null,
    ): array;

    /**
     * @param  list<string>  $codigosSolicitacao
     * @return array<string, mixed>
     */
    public function reenviar(array $codigosSolicitacao): array;
}
