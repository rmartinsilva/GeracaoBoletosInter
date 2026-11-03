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
     * @param  array{emitidas: int, enviadas: int, aguardando: int, falhas: int}  $resumo
     */
    private function mensagem(array $resumo): string
    {
        return sprintf(
            'Rotina concluída. Emitidas: %d. E-mails enviados: %d. Aguardando o boleto: %d. Falhas: %d.',
            $resumo['emitidas'],
            $resumo['enviadas'],
            $resumo['aguardando'],
            $resumo['falhas'],
        );
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
