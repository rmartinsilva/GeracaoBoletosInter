<?php

namespace App\Console\Commands;

use App\Services\EmissorDeCobrancas;
use App\Services\Inter\ExcecaoInter;
use Illuminate\Console\Command;
use Throwable;

class GerarCobrancas extends Command
{
    protected $signature = 'cobrancas:gerar';

    protected $description = 'Emite os boletos do dia e envia o e-mail quando o Banco Inter devolve o PDF';

    public function handle(): int
    {
        try {
            $emissor = $this->laravel->make(EmissorDeCobrancas::class);
            $resumo = $emissor->executar();
        } catch (Throwable $excecao) {
            $inter = $this->excecaoInter($excecao);

            if ($inter === null) {
                throw $excecao;
            }

            $this->components->error($inter->getMessage());

            return self::FAILURE;
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
