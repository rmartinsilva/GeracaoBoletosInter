<?php

namespace App\Repositories\Eloquent;

use App\Enums\SituacaoLancamento;
use App\Models\Lancamento;
use App\Repositories\Contracts\LancamentoRepositoryInterface;
use Carbon\CarbonInterface;
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

    public function pendentesDeEmail(): Collection
    {
        return $this->model->newQuery()
            ->where('situacao', SituacaoLancamento::Aberto)
            ->whereNotNull('codigo_solicitacao')
            ->whereNull('email_enviado_em')
            ->with('assinatura.cliente')
            ->orderBy('id')
            ->get();
    }

    public function registrarSolicitacao(Lancamento $lancamento, string $codigoSolicitacao): void
    {
        $lancamento->update([
            'codigo_solicitacao' => $codigoSolicitacao,
            'erro_emissao' => null,
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
        ]);
    }

    public function registrarFalha(Lancamento $lancamento, ?string $erro): void
    {
        $lancamento->update([
            'erro_emissao' => $erro,
        ]);
    }
}
