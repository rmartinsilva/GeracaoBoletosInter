<?php

namespace App\Repositories\Eloquent;

use App\DTO\Cobrancas\FiltroListagemCobranca;
use App\DTO\Cobrancas\ResumoListagemCobranca;
use App\Enums\FiltroEmissaoCobranca;
use App\Enums\FiltroSituacaoCobranca;
use App\Enums\SituacaoLancamento;
use App\Models\Lancamento;
use App\Repositories\Contracts\LancamentoRepositoryInterface;
use App\Repositories\PaginationPresenter;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LancamentoRepository implements LancamentoRepositoryInterface
{
    public function __construct(
        protected Lancamento $model
    ) {}

    public function existe(int $assinaturaId, string $competencia): bool
    {
        return $this->model->newQuery()
            ->where('assinatura_id', $assinaturaId)
            ->whereDate('competencia', $competencia)
            ->exists();
    }

    public function ultimaCompetencia(int $assinaturaId): ?string
    {
        $lancamento = $this->model->newQuery()
            ->where('assinatura_id', $assinaturaId)
            ->orderByDesc('competencia')
            ->first();

        return $lancamento?->competencia?->toDateString();
    }

    public function criar(int $assinaturaId, string $competencia, string $valor, string $vencimento): Lancamento
    {
        return $this->model->newQuery()->create([
            'assinatura_id' => $assinaturaId,
            'competencia' => $competencia,
            'valor' => $valor,
            'vencimento' => $vencimento,
            'situacao' => SituacaoLancamento::Aberto,
        ]);
    }

    public function criarAvulsa(int $clienteId, string $descricao, string $valor, string $vencimento): Lancamento
    {
        return $this->model->newQuery()->create([
            'cliente_id' => $clienteId,
            'descricao' => $descricao,
            'competencia' => $vencimento,
            'valor' => $valor,
            'vencimento' => $vencimento,
            'situacao' => SituacaoLancamento::Aberto,
        ]);
    }

    public function listarAvulsas(int $clienteId): Collection
    {
        return $this->model->newQuery()
            ->where('cliente_id', $clienteId)
            ->whereNull('assinatura_id')
            ->orderByDesc('vencimento')
            ->orderByDesc('id')
            ->get();
    }

    public function buscarDaAssinatura(int $assinaturaId, int $lancamentoId): ?Lancamento
    {
        return $this->model->newQuery()
            ->where('assinatura_id', $assinaturaId)
            ->whereKey($lancamentoId)
            ->first();
    }

    public function temPagos(int $assinaturaId): bool
    {
        return $this->model->newQuery()
            ->where('assinatura_id', $assinaturaId)
            ->where('situacao', SituacaoLancamento::Pago)
            ->exists();
    }

    public function marcarPago(Lancamento $lancamento, CarbonInterface $pagoEm): void
    {
        $lancamento->update([
            'situacao' => SituacaoLancamento::Pago,
            'pago_em' => $pagoEm,
        ]);
    }

    public function cancelar(Lancamento $lancamento): void
    {
        $lancamento->update([
            'situacao' => SituacaoLancamento::Cancelado,
            'pago_em' => null,
        ]);
    }

    public function atualizar(Lancamento $lancamento, string $valor, string $vencimento): void
    {
        $lancamento->update([
            'valor' => $valor,
            'vencimento' => $vencimento,
        ]);
    }

    public function cancelarAbertosComVencimentoApos(int $assinaturaId, string $dataFim): void
    {
        $this->model->newQuery()
            ->where('assinatura_id', $assinaturaId)
            ->where('situacao', SituacaoLancamento::Aberto)
            ->whereDate('vencimento', '>', $dataFim)
            ->update(['situacao' => SituacaoLancamento::Cancelado->value]);
    }

    public function buscarPorCompetencia(int $assinaturaId, string $competencia): ?Lancamento
    {
        return $this->model->newQuery()
            ->where('assinatura_id', $assinaturaId)
            ->whereDate('competencia', $competencia)
            ->first();
    }

    public function buscarPorCodigoSolicitacao(string $codigoSolicitacao): ?Lancamento
    {
        return $this->model->newQuery()
            ->where('codigo_solicitacao', $codigoSolicitacao)
            ->first();
    }

    public function buscarPorId(int $id): ?Lancamento
    {
        return $this->model->newQuery()->find($id);
    }

    public function pendentesDeEmail(): Collection
    {
        return $this->model->newQuery()
            ->where('situacao', SituacaoLancamento::Aberto)
            ->whereNotNull('codigo_solicitacao')
            ->whereNull('email_enviado_em')
            ->with(['assinatura.cliente', 'cliente'])
            ->orderBy('id')
            ->get();
    }

    public function avulsosEmAberto(): Collection
    {
        return $this->model->newQuery()
            ->whereNull('assinatura_id')
            ->where('situacao', SituacaoLancamento::Aberto)
            ->whereNull('email_enviado_em')
            ->with('cliente')
            ->orderBy('id')
            ->get();
    }

    public function assinaturasComFalhaDeEmissao(): Collection
    {
        return $this->model->newQuery()
            ->whereNotNull('assinatura_id')
            ->where('situacao', SituacaoLancamento::Aberto)
            ->whereNull('codigo_solicitacao')
            ->whereNull('email_enviado_em')
            ->whereNotNull('erro_emissao')
            ->with('assinatura.cliente')
            ->orderBy('id')
            ->get();
    }

    public function registrarSolicitacao(Lancamento $lancamento, string $codigoSolicitacao): void
    {
        $lancamento->update([
            'codigo_solicitacao' => $codigoSolicitacao,
            'erro_emissao' => null,
            'falha_notificada_em' => null,
        ]);
    }

    public function registrarEnvio(
        Lancamento $lancamento,
        string $linhaDigitavel,
        ?string $pixCopiaCola,
        ?string $nossoNumero,
        CarbonInterface $enviadoEm,
    ): void {
        $lancamento->update([
            'linha_digitavel' => $linhaDigitavel,
            'pix_copia_cola' => $pixCopiaCola,
            'nosso_numero' => $nossoNumero,
            'email_enviado_em' => $enviadoEm,
            'erro_emissao' => null,
            'falha_notificada_em' => null,
        ]);
    }

    public function registrarFalha(Lancamento $lancamento, ?string $erro): void
    {
        $dados = ['erro_emissao' => $erro];

        if ($erro === null || $erro !== $lancamento->erro_emissao) {
            $dados['falha_notificada_em'] = null;
        }

        $lancamento->update($dados);
    }

    public function registrarAvisoDeFalha(Lancamento $lancamento, CarbonInterface $quando): void
    {
        $lancamento->update([
            'falha_notificada_em' => $quando,
        ]);
    }

    public function paginarPorPeriodo(FiltroListagemCobranca $filtro, int $pagina, int $porPagina): PaginationPresenter
    {
        $paginator = $this->consulta($filtro, true)
            ->select('lancamentos.*')
            ->with(['assinatura.cliente', 'cliente'])
            ->orderByRaw('coalesce(cliente_assinatura.nome, cliente_avulso.nome)')
            ->orderBy('lancamentos.vencimento')
            ->orderBy('lancamentos.id')
            ->paginate($porPagina, ['lancamentos.*'], 'page', $pagina);

        return new PaginationPresenter($paginator);
    }

    public function resumirPorPeriodo(FiltroListagemCobranca $filtro): ResumoListagemCobranca
    {
        $pago = SituacaoLancamento::Pago->value;
        $aberto = SituacaoLancamento::Aberto->value;
        $cancelado = SituacaoLancamento::Cancelado->value;

        $consulta = $this->consulta($filtro, false)->toBase();
        $consulta->columns = null;

        $totais = $consulta->selectRaw(
            'count(*) as quantidade,
            coalesce(sum(lancamentos.valor), 0) as valor,
            coalesce(sum(case when lancamentos.situacao = ? then 1 else 0 end), 0) as pagas,
            coalesce(sum(case when lancamentos.situacao = ? then lancamentos.valor else 0 end), 0) as valor_pago,
            coalesce(sum(case when lancamentos.situacao = ? then 1 else 0 end), 0) as abertas,
            coalesce(sum(case when lancamentos.situacao = ? then lancamentos.valor else 0 end), 0) as valor_aberto,
            coalesce(sum(case when lancamentos.situacao = ? and lancamentos.vencimento < ? then 1 else 0 end), 0) as vencidas,
            coalesce(sum(case when lancamentos.situacao = ? then 1 else 0 end), 0) as canceladas,
            coalesce(sum(case when lancamentos.erro_emissao is not null then 1 else 0 end), 0) as falhas,
            coalesce(sum(case when lancamentos.codigo_solicitacao is null and lancamentos.situacao = ? and lancamentos.erro_emissao is null then 1 else 0 end), 0) as sem_boleto,
            coalesce(sum(case when lancamentos.codigo_solicitacao is not null then 1 else 0 end), 0) as emitidas',
            [$pago, $pago, $aberto, $aberto, $aberto, $filtro->hoje, $cancelado, $aberto],
        )->first();

        return new ResumoListagemCobranca(
            quantidade: (int) $totais->quantidade,
            valor: $this->dinheiro($totais->valor),
            pagas: (int) $totais->pagas,
            valor_pago: $this->dinheiro($totais->valor_pago),
            abertas: (int) $totais->abertas,
            valor_aberto: $this->dinheiro($totais->valor_aberto),
            vencidas: (int) $totais->vencidas,
            canceladas: (int) $totais->canceladas,
            falhas: (int) $totais->falhas,
            sem_boleto: (int) $totais->sem_boleto,
            emitidas: (int) $totais->emitidas,
        );
    }

    /**
     * Cobranças do período, com o cliente da assinatura ou da cobrança avulsa.
     *
     * @return Builder<Lancamento>
     */
    private function consulta(FiltroListagemCobranca $filtro, bool $aplicarRecorte): Builder
    {
        $consulta = $this->model->newQuery()
            ->leftJoin('assinaturas', 'assinaturas.id', '=', 'lancamentos.assinatura_id')
            ->leftJoin('clientes as cliente_assinatura', 'cliente_assinatura.id', '=', 'assinaturas.cliente_id')
            ->leftJoin('clientes as cliente_avulso', 'cliente_avulso.id', '=', 'lancamentos.cliente_id')
            ->whereDate('lancamentos.vencimento', '>=', $filtro->de)
            ->whereDate('lancamentos.vencimento', '<=', $filtro->ate);

        if ($filtro->busca !== null) {
            $termo = '%'.$filtro->busca.'%';
            $consulta->where(function (Builder $consulta) use ($termo): void {
                $consulta->where('cliente_assinatura.nome', 'like', $termo)
                    ->orWhere('cliente_avulso.nome', 'like', $termo)
                    ->orWhere('assinaturas.descricao', 'like', $termo)
                    ->orWhere('lancamentos.descricao', 'like', $termo);
            });
        }

        if ($aplicarRecorte) {
            $this->aplicarRecorte($consulta, $filtro);
        }

        return $consulta;
    }

    /**
     * @param  Builder<Lancamento>  $consulta
     */
    private function aplicarRecorte(Builder $consulta, FiltroListagemCobranca $filtro): void
    {
        if ($filtro->situacao === FiltroSituacaoCobranca::Vencido) {
            $consulta->where('lancamentos.situacao', SituacaoLancamento::Aberto)
                ->whereDate('lancamentos.vencimento', '<', $filtro->hoje);
        } elseif ($filtro->situacao !== null) {
            $consulta->where('lancamentos.situacao', $filtro->situacao->value);
        }

        match ($filtro->emissao) {
            FiltroEmissaoCobranca::Emitida => $consulta->whereNotNull('lancamentos.codigo_solicitacao'),
            FiltroEmissaoCobranca::SemBoleto => $consulta
                ->whereNull('lancamentos.codigo_solicitacao')
                ->where('lancamentos.situacao', SituacaoLancamento::Aberto)
                ->whereNull('lancamentos.erro_emissao'),
            FiltroEmissaoCobranca::Falha => $consulta->whereNotNull('lancamentos.erro_emissao'),
            null => null,
        };
    }

    private function dinheiro(mixed $valor): string
    {
        return number_format((float) $valor, 2, '.', '');
    }
}
