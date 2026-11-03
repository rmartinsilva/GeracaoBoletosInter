<?php

namespace App\Console\Commands;

use App\Enums\Inter\FiltrarDataPor;
use App\Services\DataHoraService;
use App\Services\ImportadorDeClientes;
use App\Services\Inter\ExcecaoInter;
use Illuminate\Console\Command;
use Throwable;

class ImportarClientes extends Command
{
    protected $signature = 'clientes:importar
        {--de= : Data inicial no formato AAAA-MM-DD. O padrão é cinco anos atrás}
        {--ate= : Data final no formato AAAA-MM-DD. O padrão é hoje}
        {--por=emissao : Filtra o período por emissao, vencimento ou pagamento}';

    protected $description = 'Cadastra os pagadores das cobranças do Banco Inter que ainda não existem como clientes';

    public function handle(): int
    {
        $por = match ($this->option('por')) {
            'emissao' => FiltrarDataPor::Emissao,
            'vencimento' => FiltrarDataPor::Vencimento,
            'pagamento' => FiltrarDataPor::Pagamento,
            default => null,
        };

        if ($por === null) {
            $this->components->error('O filtro --por aceita emissao, vencimento ou pagamento.');

            return self::FAILURE;
        }

        $hoje = $this->laravel->make(DataHoraService::class)->agora();
        $de = $this->option('de');
        $ate = $this->option('ate');

        try {
            $resumo = $this->laravel->make(ImportadorDeClientes::class)->executar(
                dataInicial: is_string($de) && $de !== '' ? $de : $hoje->subYears(5)->toDateString(),
                dataFinal: is_string($ate) && $ate !== '' ? $ate : $hoje->toDateString(),
                por: $por,
            );
        } catch (Throwable $excecao) {
            $inter = $this->excecaoInter($excecao);

            if ($inter === null) {
                throw $excecao;
            }

            $this->components->error($inter->getMessage());

            return self::FAILURE;
        }

        $this->line('Importados: '.$resumo['importados']);
        $this->line('Sem e-mail: '.$resumo['sem_email']);
        $this->line('Já cadastrados: '.$resumo['existentes']);
        $this->line('Ignorados: '.$resumo['ignorados']);
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
