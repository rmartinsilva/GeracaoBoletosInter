<?php

namespace App\Console\Commands;

use App\Services\EmissorDeCobrancas;
use App\Services\Inter\ExcecaoInter;
use Illuminate\Console\Command;
use Throwable;

class ProcessarFilaDeEmissoes extends Command
{
    protected $signature = 'cobrancas:processar-fila';

    protected $description = 'Envia ao Banco Inter a próxima cobrança da fila';

    public function handle(): int
    {
        try {
            $resumo = $this->laravel->make(EmissorDeCobrancas::class)->processarProxima();
        } catch (Throwable $excecao) {
            $inter = $this->excecaoInter($excecao);

            if ($inter === null) {
                throw $excecao;
            }

            $this->components->error($inter->getMessage());

            return self::FAILURE;
        }

        if ($resumo === null) {
            $this->line('Fila vazia.');

            return self::SUCCESS;
        }

        $this->line('Emitidas: '.$resumo['emitidas']);
        $this->line('E-mails enviados: '.$resumo['enviadas']);
        $this->line('Aguardando o boleto: '.$resumo['aguardando']);
        $this->line('Falhas: '.$resumo['falhas']);

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
