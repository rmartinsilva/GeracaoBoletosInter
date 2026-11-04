<?php

namespace App\Console\Commands;

use App\Services\EmissorDeCobrancas;
use App\Services\Inter\ExcecaoInter;
use Illuminate\Console\Command;
use Throwable;

class GerarCobrancas extends Command
{
    protected $signature = 'cobrancas:gerar';

    protected $description = 'Coloca na fila os boletos do dia e os que ainda precisam de e-mail';

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

        $this->line('Na fila: '.$resumo['enfileiradas']);
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
