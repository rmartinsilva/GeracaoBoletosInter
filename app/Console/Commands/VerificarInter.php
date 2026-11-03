<?php

namespace App\Console\Commands;

use App\Services\Inter\AutenticacaoInter;
use App\Services\Inter\ConfiguracaoInter;
use App\Services\Inter\ExcecaoInter;
use Illuminate\Console\Command;
use Throwable;

class VerificarInter extends Command
{
    protected $signature = 'inter:verificar {--renovar : Ignora o token em cache e autentica de novo}';

    protected $description = 'Autentica na API de cobrança do Banco Inter no ambiente configurado';

    public function handle(): int
    {
        try {
            $configuracao = $this->laravel->make(ConfiguracaoInter::class);
            $autenticacao = $this->laravel->make(AutenticacaoInter::class);
            $token = $autenticacao->autenticar((bool) $this->option('renovar'));
        } catch (Throwable $excecao) {
            $inter = $this->excecaoInter($excecao);

            if ($inter === null) {
                throw $excecao;
            }

            $this->components->error($inter->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Ambiente: '.$configuracao->ambiente->rotulo());
        $this->line('URL: '.$configuracao->urlBase);
        $this->line('Conta corrente: '.($configuracao->contaCorrente ?? 'não informada'));
        $this->components->info($token->reutilizado
            ? 'Autenticação concluída com o token que já estava em cache.'
            : 'Autenticação concluída. O token foi armazenado em cache.');
        $this->line('Validade informada pelo Inter: '.$token->expiraEm.' segundos.');

        if ($token->escopos !== '') {
            $this->line('Escopos: '.$token->escopos);
        }

        $this->line('A troca de sandbox para produção é a variável INTER_AMBIENTE.');

        return self::SUCCESS;
    }

    private function excecaoInter(Throwable $excecao): ?ExcecaoInter
    {
        while (! $excecao instanceof ExcecaoInter) {
            $anterior = $excecao->getPrevious();

            if (! $anterior instanceof Throwable) {
                return null;
            }

            $excecao = $anterior;
        }

        return $excecao;
    }
}
