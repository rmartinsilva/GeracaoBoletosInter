<?php

namespace App\Services\Inter;

use App\Services\Inter\Contracts\WebhookCobrancaInterInterface;

/**
 * Webhook da API Cobrança v3.
 *
 * O reenvio usa /cobranca/v3/cobrancas/webhook/callbacks/retry, o mesmo prefixo
 * das demais rotas de webhook e o endereço do exemplo de código da referência.
 */
class WebhookCobrancaInterService implements WebhookCobrancaInterInterface
{
    public function __construct(private ClienteHttpInter $http) {}

    public function cadastrar(string $url): void
    {
        $url = trim($url);

        if (! str_starts_with($url, 'https://') || filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new ExcecaoInter('A URL do webhook deve começar com https://.');
        }

        $this->http->enviar('PUT', ConfiguracaoInter::CAMINHO_COBRANCAS.'/webhook', [
            'webhookUrl' => $url,
        ], [204]);
    }

    public function obter(): array
    {
        return $this->http->enviar('GET', ConfiguracaoInter::CAMINHO_COBRANCAS.'/webhook') ?? [];
    }

    public function excluir(): void
    {
        $this->http->enviar('DELETE', ConfiguracaoInter::CAMINHO_COBRANCAS.'/webhook', statusSucesso: [204]);
    }

    public function listarCallbacks(
        string $dataHoraInicio,
        string $dataHoraFim,
        int $pagina = 0,
        int $tamanhoPagina = 20,
        ?string $codigoSolicitacao = null,
    ): array {
        if ($pagina < 0) {
            throw new ExcecaoInter('A página de callbacks não pode ser negativa.');
        }

        if ($tamanhoPagina < 10 || $tamanhoPagina > 50) {
            throw new ExcecaoInter('A página de callbacks deve ter de 10 a 50 itens.');
        }

        $consulta = [
            'dataHoraInicio' => ValorInter::dataHora($dataHoraInicio, 'data e hora inicial'),
            'dataHoraFim' => ValorInter::dataHora($dataHoraFim, 'data e hora final'),
            'pagina' => $pagina,
            'tamanhoPagina' => $tamanhoPagina,
        ];

        if ($codigoSolicitacao !== null && trim($codigoSolicitacao) !== '') {
            $consulta['codigoSolicitacao'] = ValorInter::codigo($codigoSolicitacao);
        }

        return $this->http->enviar(
            'GET',
            ConfiguracaoInter::CAMINHO_COBRANCAS.'/webhook/callbacks',
            $consulta,
        ) ?? [];
    }

    public function reenviar(array $codigosSolicitacao): array
    {
        if ($codigosSolicitacao === [] || count($codigosSolicitacao) > 50) {
            throw new ExcecaoInter('Informe de 1 a 50 códigos de solicitação para reenviar os callbacks.');
        }

        $codigos = [];

        foreach ($codigosSolicitacao as $codigo) {
            if (! is_string($codigo)) {
                throw new ExcecaoInter('Os códigos de solicitação devem ser texto.');
            }

            $codigos[] = ValorInter::codigo($codigo);
        }

        return $this->http->enviar(
            'POST',
            ConfiguracaoInter::CAMINHO_COBRANCAS.'/webhook/callbacks/retry',
            ['codigoSolicitacao' => $codigos],
        ) ?? [];
    }
}
