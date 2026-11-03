<?php

namespace App\Services;

use App\Enums\Periodicidade;
use App\Exceptions\RegraNegocioException;
use Carbon\CarbonImmutable;

/**
 * Calcula as competências e os vencimentos de uma assinatura.
 * Todas as datas são dias de calendário (Y-m-d), sem horário.
 */
class PlanejadorDeLancamentos
{
    private const LIMITE = 120;

    /**
     * Períodos do início até o mês de $hoje. Se o primeiro vencimento ainda é futuro, devolve só ele.
     * Quando o intervalo passa de 120 períodos, ficam os mais recentes.
     *
     * @return array{periodos: list<array{competencia: string, vencimento: string}>, limitado: bool}
     */
    public function planejar(
        Periodicidade $periodicidade,
        string $dataInicio,
        ?string $dataFim,
        ?int $diaVencimento,
        string $hoje,
    ): array {
        $inicio = $this->dia($dataInicio);
        $fim = $dataFim !== null ? $this->dia($dataFim) : null;

        $diaVencimento = $this->exigirDia($diaVencimento);
        $limiteDoMes = $this->dia($hoje)->endOfMonth()->startOfDay();
        $atual = $this->primeiroVencimento($inicio, $diaVencimento, $periodicidade);
        $periodos = [];

        while (true) {
            if ($fim !== null && $atual->gt($fim)) {
                break;
            }

            $alemDoMesAtual = $atual->gt($limiteDoMes);

            if ($alemDoMesAtual && $periodos !== []) {
                break;
            }

            $periodos[] = [
                'competencia' => $atual->startOfMonth()->toDateString(),
                'vencimento' => $atual->toDateString(),
            ];

            if ($alemDoMesAtual) {
                break;
            }

            $atual = $this->avancar($atual, $diaVencimento, $periodicidade);
        }

        $limitado = count($periodos) > self::LIMITE;

        if ($limitado) {
            $periodos = array_values(array_slice($periodos, -self::LIMITE));
        }

        return ['periodos' => $periodos, 'limitado' => $limitado];
    }

    /**
     * Período seguinte ao último já gravado. Sem lançamentos, devolve o primeiro previsto.
     *
     * @return array{competencia: string, vencimento: string}
     */
    public function proximo(
        Periodicidade $periodicidade,
        ?string $ultimaCompetencia,
        string $dataInicio,
        ?string $dataFim,
        ?int $diaVencimento,
    ): array {
        if ($ultimaCompetencia === null) {
            $planejado = $this->planejar($periodicidade, $dataInicio, $dataFim, $diaVencimento, $dataInicio);

            if ($planejado['periodos'] === []) {
                throw new RegraNegocioException('Não há lançamento para esta assinatura.');
            }

            return $planejado['periodos'][0];
        }

        $diaVencimento = $this->exigirDia($diaVencimento);
        $base = $this->dia($ultimaCompetencia)->startOfMonth();
        $atual = $periodicidade === Periodicidade::Anual
            ? $this->vencimentoNoMes($base->addYear(), $diaVencimento)
            : $this->vencimentoNoMes($base->addMonth(), $diaVencimento);

        if ($dataFim !== null && $atual->gt($this->dia($dataFim))) {
            throw new RegraNegocioException('Não há próximo lançamento: a data fica depois do encerramento da assinatura.');
        }

        return [
            'competencia' => $atual->startOfMonth()->toDateString(),
            'vencimento' => $atual->toDateString(),
        ];
    }

    /**
     * Período cuja cobrança deve ser emitida em $hoje, ou null se hoje não é o dia de gerar.
     *
     * Quando o dia de geração é posterior ao vencimento, o boleto sai no mês anterior.
     * O dia 31 acompanha o último dia do mês.
     *
     * @return array{competencia: string, vencimento: string}|null
     */
    public function periodoDoDia(
        Periodicidade $periodicidade,
        string $dataInicio,
        ?string $dataFim,
        ?int $diaVencimento,
        ?int $diaGeracao,
        string $hoje,
    ): ?array {
        $hoje = $this->dia($hoje);
        $inicio = $this->dia($dataInicio);
        $fim = $dataFim !== null ? $this->dia($dataFim) : null;

        $diaVencimento = $this->exigirDia($diaVencimento);

        if ($diaGeracao === null) {
            throw new RegraNegocioException('Informe o dia de geração da cobrança.');
        }

        if (! $this->caiNesteDia($hoje, $diaGeracao)) {
            return null;
        }

        $mesVencimento = $hoje->startOfMonth();

        if ($diaGeracao > $diaVencimento) {
            $mesVencimento = $mesVencimento->addMonth();
        }

        $vencimento = $this->vencimentoNoMes($mesVencimento, $diaVencimento);

        if ($periodicidade === Periodicidade::Anual && $vencimento->month !== $inicio->month) {
            return null;
        }

        if ($vencimento->lt($inicio)) {
            return null;
        }

        if ($fim !== null && $vencimento->gt($fim)) {
            return null;
        }

        return [
            'competencia' => $vencimento->startOfMonth()->toDateString(),
            'vencimento' => $vencimento->toDateString(),
        ];
    }

    private function exigirDia(?int $diaVencimento): int
    {
        if ($diaVencimento === null) {
            throw new RegraNegocioException('Informe o dia de vencimento para gerar os lançamentos.');
        }

        return $diaVencimento;
    }

    private function primeiroVencimento(CarbonImmutable $inicio, int $dia, Periodicidade $periodicidade): CarbonImmutable
    {
        $noPeriodo = $this->vencimentoNoMes($inicio, $dia);

        if ($noPeriodo->lt($inicio)) {
            return $periodicidade === Periodicidade::Anual
                ? $this->vencimentoNoMes($inicio->addYear(), $dia)
                : $this->vencimentoNoMes($inicio->addMonth(), $dia);
        }

        return $noPeriodo;
    }

    private function avancar(CarbonImmutable $vencimento, int $dia, Periodicidade $periodicidade): CarbonImmutable
    {
        $mes = $vencimento->startOfMonth();

        return $periodicidade === Periodicidade::Anual
            ? $this->vencimentoNoMes($mes->addYear(), $dia)
            : $this->vencimentoNoMes($mes->addMonth(), $dia);
    }

    private function vencimentoNoMes(CarbonImmutable $referencia, int $dia): CarbonImmutable
    {
        $mes = $referencia->startOfMonth();

        return $mes->setDay(min($dia, $mes->daysInMonth));
    }

    private function dia(string $data): CarbonImmutable
    {
        return CarbonImmutable::parse($data)->startOfDay();
    }

    private function caiNesteDia(CarbonImmutable $data, int $dia): bool
    {
        return $data->day === min($dia, $data->daysInMonth);
    }
}
