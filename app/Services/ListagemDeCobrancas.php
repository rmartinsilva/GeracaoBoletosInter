<?php

namespace App\Services;

use App\DTO\Cobrancas\FiltroListagemCobranca;
use App\DTO\Cobrancas\ResumoListagemCobranca;
use App\Enums\FiltroEmissaoCobranca;
use App\Enums\FiltroSituacaoCobranca;
use App\Repositories\Contracts\LancamentoRepositoryInterface;
use App\Repositories\PaginationPresenter;
use Carbon\CarbonImmutable;

class ListagemDeCobrancas
{
    public function __construct(
        protected LancamentoRepositoryInterface $lancamentos,
        protected DataHoraService $dataHora,
    ) {}

    /**
     * Interpreta o período e os recortes da tela. Sem datas válidas, usa o mês atual em Brasília.
     */
    public function montarFiltro(?string $de, ?string $ate, ?string $busca, ?string $situacao, ?string $emissao): FiltroListagemCobranca
    {
        $hoje = $this->dataHora->agora();
        $inicio = $this->data($de) ?? $hoje->startOfMonth()->toDateString();
        $fim = $this->data($ate) ?? $hoje->endOfMonth()->toDateString();

        if ($inicio > $fim) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        $busca = trim((string) $busca);

        return new FiltroListagemCobranca(
            de: $inicio,
            ate: $fim,
            busca: $busca !== '' ? $busca : null,
            situacao: FiltroSituacaoCobranca::tryFrom((string) $situacao),
            emissao: FiltroEmissaoCobranca::tryFrom((string) $emissao),
            hoje: $hoje->toDateString(),
        );
    }

    /**
     * Cobranças do período, da página pedida.
     */
    public function listar(FiltroListagemCobranca $filtro, int $pagina, int $porPagina = 20): PaginationPresenter
    {
        return $this->lancamentos->paginarPorPeriodo($filtro, max(1, $pagina), $porPagina);
    }

    /**
     * Totais do período e da busca, sem o recorte de situação ou de emissão.
     */
    public function resumir(FiltroListagemCobranca $filtro): ResumoListagemCobranca
    {
        return $this->lancamentos->resumirPorPeriodo($filtro);
    }

    /**
     * Aceita só uma data de calendário. Qualquer outro texto volta ao mês atual.
     */
    private function data(?string $valor): ?string
    {
        if ($valor === null || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return null;
        }

        $data = CarbonImmutable::createFromFormat('!Y-m-d', $valor);

        if ($data === false || $data->format('Y-m-d') !== $valor) {
            return null;
        }

        return $valor;
    }
}
