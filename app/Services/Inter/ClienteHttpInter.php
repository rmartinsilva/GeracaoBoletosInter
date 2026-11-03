<?php

namespace App\Services\Inter;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClienteHttpInter
{
    public function __construct(
        private ConfiguracaoInter $configuracao,
        private AutenticacaoInter $autenticacao,
    ) {}

    /**
     * @param  array<string, mixed>  $dados
     * @param  list<int>  $statusSucesso
     * @return array<mixed>|null
     */
    public function enviar(
        string $metodo,
        string $caminho,
        array $dados = [],
        array $statusSucesso = [200],
        bool $renovarToken = false,
    ): ?array {
        $token = $this->autenticacao->autenticar($renovarToken);

        $requisicao = Http::withOptions([
            ...$this->configuracao->opcoesTls(),
            'allow_redirects' => false,
        ])
            ->withToken($token->acesso)
            ->acceptJson()
            ->asJson()
            ->timeout($this->configuracao->timeout)
            ->withHeaders($this->configuracao->cabecalhos());

        $url = $this->configuracao->urlBase.$caminho;

        try {
            $resposta = match (strtoupper($metodo)) {
                'GET' => $requisicao->get($url, $dados),
                'POST' => $requisicao->post($url, $dados),
                'PUT' => $requisicao->put($url, $dados),
                'PATCH' => $requisicao->patch($url, $dados),
                'DELETE' => $requisicao->delete($url, $dados),
                default => throw new ExcecaoInter('Método HTTP não suportado na API do Banco Inter.'),
            };
        } catch (ConnectionException $excecao) {
            throw ExcecaoInter::conexao($excecao, $this->configuracao->ambiente);
        }

        if ($resposta->status() === 401 && ! $renovarToken) {
            return $this->enviar($metodo, $caminho, $dados, $statusSucesso, renovarToken: true);
        }

        return $this->interpretar($resposta, $statusSucesso, $caminho);
    }

    /**
     * @param  list<int>  $statusSucesso
     * @return array<mixed>|null
     */
    private function interpretar(Response $resposta, array $statusSucesso, string $caminho): ?array
    {
        $corpo = $resposta->json();

        if (in_array($resposta->status(), $statusSucesso, true)) {
            return is_array($corpo) ? $corpo : null;
        }

        Log::warning('Falha na API de cobrança do Banco Inter.', [
            'status' => $resposta->status(),
            'caminho' => $caminho,
            'ambiente' => $this->configuracao->ambiente->value,
        ]);

        if (! is_array($corpo)) {
            throw new ExcecaoInter(
                'A API do Banco Inter respondeu HTTP '.$resposta->status().' sem um JSON reconhecível.',
                $resposta->status(),
            );
        }

        $titulo = is_string($corpo['title'] ?? null) ? $corpo['title'] : null;
        $detalhe = is_string($corpo['detail'] ?? null) ? $corpo['detail'] : null;
        $mensagem = match (true) {
            $titulo !== null && $detalhe !== null => $titulo.' '.$detalhe,
            $detalhe !== null => $detalhe,
            $titulo !== null => $titulo,
            default => 'A API do Banco Inter recusou a requisição.',
        };

        $violacoes = [];
        $listaViolacoes = $corpo['violacoes'] ?? [];

        if (is_array($listaViolacoes)) {
            foreach ($listaViolacoes as $violacao) {
                if (! is_array($violacao)) {
                    continue;
                }

                $violacoes[] = $violacao;
                $razao = is_string($violacao['razao'] ?? null) ? $violacao['razao'] : null;
                $propriedade = is_string($violacao['propriedade'] ?? null) ? $violacao['propriedade'] : null;

                if ($razao === null) {
                    continue;
                }

                $mensagem .= ' '.$razao.($propriedade !== null ? " ({$propriedade})" : '');
            }
        }

        $segredo = $this->configuracao->clientSecret;

        if (strlen($segredo) >= 8) {
            $mensagem = str_replace($segredo, '[oculto]', $mensagem);
        }

        throw new ExcecaoInter(
            'A API do Banco Inter respondeu HTTP '.$resposta->status().': '.$mensagem,
            $resposta->status(),
            $corpo,
            $violacoes,
        );
    }
}
