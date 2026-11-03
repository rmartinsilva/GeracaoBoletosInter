<?php

namespace App\Services\Inter;

use App\DTO\Inter\EditarCobrancaDTO;
use App\DTO\Inter\EmitirCobrancaDTO;
use App\DTO\Inter\FiltroCobrancasDTO;
use App\Enums\Inter\AmbienteInter;
use App\Enums\Inter\FormaPagamentoSandbox;
use App\Services\Inter\Contracts\CobrancaInterInterface;

/**
 * Cliente da API Cobrança v3 (boleto com Pix).
 *
 * A referência oficial está em https://developers.inter.co/references/cobranca-bolepix
 */
class CobrancaInterService implements CobrancaInterInterface
{
    public function __construct(
        private ClienteHttpInter $http,
        private ConfiguracaoInter $configuracao,
    ) {}

    public function emitir(EmitirCobrancaDTO $cobranca): array
    {
        $resposta = $this->http->enviar('POST', ConfiguracaoInter::CAMINHO_COBRANCAS, $cobranca->paraPayload());

        if (! is_string($resposta['codigoSolicitacao'] ?? null) || $resposta['codigoSolicitacao'] === '') {
            throw new ExcecaoInter('A API de cobrança não retornou o codigoSolicitacao.');
        }

        return $resposta;
    }

    public function consultar(string $codigoSolicitacao): array
    {
        return $this->http->enviar(
            'GET',
            ConfiguracaoInter::CAMINHO_COBRANCAS.'/'.ValorInter::codigo($codigoSolicitacao),
        ) ?? [];
    }

    public function listar(FiltroCobrancasDTO $filtro): array
    {
        return $this->http->enviar(
            'GET',
            ConfiguracaoInter::CAMINHO_COBRANCAS,
            $filtro->paraConsulta(),
        ) ?? [];
    }

    public function editar(string $codigoSolicitacao, EditarCobrancaDTO $dados): array
    {
        return $this->http->enviar(
            'PATCH',
            ConfiguracaoInter::CAMINHO_COBRANCAS.'/'.ValorInter::codigo($codigoSolicitacao),
            $dados->paraPayload(),
        ) ?? [];
    }

    public function consultarEdicao(string $codigoEdicao): array
    {
        return $this->http->enviar(
            'GET',
            ConfiguracaoInter::CAMINHO_COBRANCAS.'/edicao/'.ValorInter::codigo($codigoEdicao),
        ) ?? [];
    }

    public function recuperarPdf(string $codigoSolicitacao): string
    {
        $resposta = $this->http->enviar(
            'GET',
            ConfiguracaoInter::CAMINHO_COBRANCAS.'/'.ValorInter::codigo($codigoSolicitacao).'/pdf',
        );
        $base64 = $resposta['pdf'] ?? null;

        if (! is_string($base64) || $base64 === '') {
            throw new ExcecaoInter('A API de cobrança não retornou o PDF.');
        }

        $conteudo = base64_decode($base64, true);

        if ($conteudo === false) {
            throw new ExcecaoInter('O PDF retornado pela API de cobrança não é um base64 válido.');
        }

        return $conteudo;
    }

    public function cancelar(string $codigoSolicitacao, string $motivo): void
    {
        $motivo = trim($motivo);

        if ($motivo === '' || mb_strlen($motivo) > 50) {
            throw new ExcecaoInter('O motivo do cancelamento deve ter de 1 a 50 caracteres.');
        }

        $this->http->enviar(
            'POST',
            ConfiguracaoInter::CAMINHO_COBRANCAS.'/'.ValorInter::codigo($codigoSolicitacao).'/cancelar',
            ['motivoCancelamento' => $motivo],
            [202],
        );
    }

    public function sumario(FiltroCobrancasDTO $filtro): array
    {
        $resposta = $this->http->enviar(
            'GET',
            ConfiguracaoInter::CAMINHO_COBRANCAS.'/sumario',
            $filtro->paraConsulta(sumario: true),
        ) ?? [];

        return array_is_list($resposta) ? $resposta : [];
    }

    public function pagarSandbox(string $codigoSolicitacao, FormaPagamentoSandbox $forma): void
    {
        if ($this->configuracao->ambiente !== AmbienteInter::Sandbox) {
            throw new ExcecaoInter('O pagamento simulado de cobrança existe apenas no sandbox do Banco Inter.');
        }

        $this->http->enviar(
            'POST',
            ConfiguracaoInter::CAMINHO_COBRANCAS.'/'.ValorInter::codigo($codigoSolicitacao).'/pagar',
            ['pagarCom' => $forma->value],
            [204],
        );
    }
}
