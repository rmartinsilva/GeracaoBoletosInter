<?php

namespace App\Services\Inter\Contracts;

use App\DTO\Inter\EditarCobrancaDTO;
use App\DTO\Inter\EmitirCobrancaDTO;
use App\DTO\Inter\FiltroCobrancasDTO;
use App\Enums\Inter\FormaPagamentoSandbox;

interface CobrancaInterInterface
{
    /**
     * Emite a cobrança. A geração do boleto e do Pix é assíncrona.
     *
     * @return array<string, mixed>
     */
    public function emitir(EmitirCobrancaDTO $cobranca): array;

    /**
     * @return array<string, mixed>
     */
    public function consultar(string $codigoSolicitacao): array;

    /**
     * @return array<string, mixed>
     */
    public function listar(FiltroCobrancasDTO $filtro): array;

    /**
     * @return array<string, mixed>
     */
    public function editar(string $codigoSolicitacao, EditarCobrancaDTO $dados): array;

    /**
     * @return array<string, mixed>
     */
    public function consultarEdicao(string $codigoEdicao): array;

    public function recuperarPdf(string $codigoSolicitacao): string;

    public function cancelar(string $codigoSolicitacao, string $motivo): void;

    /**
     * @return array<int, mixed>
     */
    public function sumario(FiltroCobrancasDTO $filtro): array;

    public function pagarSandbox(string $codigoSolicitacao, FormaPagamentoSandbox $forma): void;
}
