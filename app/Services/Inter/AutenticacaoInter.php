<?php

namespace App\Services\Inter;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutenticacaoInter
{
    public function __construct(private ConfiguracaoInter $configuracao) {}

    /**
     * Obtém um token OAuth de client credentials e o reutiliza até perto do vencimento.
     */
    public function autenticar(bool $renovar = false): TokenInter
    {
        $chave = $this->chaveCache();

        if ($renovar) {
            Cache::forget($chave);
        } else {
            $emCache = Cache::get($chave);

            if (
                is_array($emCache)
                && is_string($emCache['acesso'] ?? null)
                && $emCache['acesso'] !== ''
            ) {
                return new TokenInter(
                    acesso: $emCache['acesso'],
                    expiraEm: (int) ($emCache['expira_em'] ?? 0),
                    escopos: (string) ($emCache['escopos'] ?? ''),
                    reutilizado: true,
                );
            }
        }

        try {
            $resposta = Http::withOptions([
                ...$this->configuracao->opcoesTls(),
                'allow_redirects' => false,
            ])
                ->asForm()
                ->acceptJson()
                ->timeout($this->configuracao->timeout)
                ->post($this->configuracao->urlBase.'/oauth/v2/token', [
                    'client_id' => $this->configuracao->clientId,
                    'client_secret' => $this->configuracao->clientSecret,
                    'grant_type' => 'client_credentials',
                    'scope' => $this->configuracao->escopos,
                ]);
        } catch (ConnectionException $excecao) {
            throw ExcecaoInter::conexao($excecao, $this->configuracao->ambiente);
        }

        $corpo = $resposta->json();

        if (! $resposta->successful() || ! is_array($corpo) || ! is_string($corpo['access_token'] ?? null) || $corpo['access_token'] === '') {
            Log::warning('Falha ao autenticar na API do Banco Inter.', [
                'status' => $resposta->status(),
                'ambiente' => $this->configuracao->ambiente->value,
            ]);

            throw new ExcecaoInter(
                $this->mensagemFalha($corpo, $resposta->status()),
                $resposta->status(),
                is_array($corpo) ? $corpo : null,
            );
        }

        $expiraEm = (int) ($corpo['expires_in'] ?? 0);

        if ($expiraEm <= 0) {
            $expiraEm = 3600;
        }

        $margem = $this->configuracao->margemTokenSegundos;
        $ttl = $expiraEm > $margem ? $expiraEm - $margem : max(1, intdiv($expiraEm, 2));
        $escopos = is_string($corpo['scope'] ?? null) ? $corpo['scope'] : $this->configuracao->escopos;

        Cache::put($chave, [
            'acesso' => $corpo['access_token'],
            'expira_em' => $expiraEm,
            'escopos' => $escopos,
        ], $ttl);

        return new TokenInter(
            acesso: $corpo['access_token'],
            expiraEm: $expiraEm,
            escopos: $escopos,
            reutilizado: false,
        );
    }

    private function chaveCache(): string
    {
        return 'inter.oauth.'.hash('sha256', implode('|', [
            $this->configuracao->ambiente->value,
            $this->configuracao->urlBase,
            $this->configuracao->clientId,
            $this->configuracao->certificado,
            $this->configuracao->escopos,
        ]));
    }

    private function mensagemFalha(mixed $corpo, int $status): string
    {
        $descricao = 'Falha ao autenticar na API do Banco Inter.';

        if (is_array($corpo)) {
            $texto = $corpo['error_description'] ?? $corpo['detail'] ?? $corpo['title'] ?? null;

            if (is_string($texto) && $texto !== '') {
                $descricao = 'Falha ao autenticar na API do Banco Inter: '.$texto;
            }
        }

        if ($status > 0) {
            $descricao .= " (HTTP {$status})";
        }

        return $this->ocultarSegredo($descricao);
    }

    private function ocultarSegredo(string $mensagem): string
    {
        $segredo = $this->configuracao->clientSecret;

        if (strlen($segredo) < 8) {
            return $mensagem;
        }

        return str_replace($segredo, '[oculto]', $mensagem);
    }
}
