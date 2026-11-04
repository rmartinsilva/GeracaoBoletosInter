<?php

namespace App\Http\Controllers\Painel;

use App\Services\EmissorDeCobrancas;
use App\Services\Inter\ExcecaoInter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class GeracaoDeCobrancasController extends PainelController
{
    public function __construct(protected Request $request)
    {
        parent::__construct($request);
    }

    /**
     * Repete a rotina das 08:00. O emissor só é resolvido aqui, para o dashboard não abrir o certificado.
     */
    public function executar(): RedirectResponse
    {
        $this->checkPermissionOrFail('cliente_editar');

        try {
            $resumo = app(EmissorDeCobrancas::class)->executar();
        } catch (Throwable $excecao) {
            $inter = $this->excecaoInter($excecao);

            if ($inter === null) {
                report($excecao);

                return redirect()
                    ->route('painel.dashboard')
                    ->withErrors(['error' => 'Não foi possível executar a geração das cobranças.']);
            }

            return redirect()
                ->route('painel.dashboard')
                ->withErrors(['error' => $inter->getMessage()]);
        }

        return redirect()
            ->route('painel.dashboard')
            ->with('success', $this->mensagem($resumo));
    }

    /**
     * @param  array{enfileiradas: int, falhas: int}  $resumo
     */
    private function mensagem(array $resumo): string
    {
        if ($resumo['enfileiradas'] === 0) {
            return 'Nenhuma cobrança entrou na fila.';
        }

        $quantidade = $resumo['enfileiradas'] === 1
            ? '1 cobrança entrou na fila.'
            : $resumo['enfileiradas'].' cobranças entraram na fila.';

        return $quantidade.' O banco recebe uma a cada minuto.';
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
