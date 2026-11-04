<?php

namespace App\Services;

use App\Enums\Inter\SituacaoCobranca;
use App\Enums\SituacaoLancamento;
use App\Models\FilaEmissao;
use App\Models\Lancamento;
use App\Repositories\Contracts\LancamentoRepositoryInterface;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * Aplica o aviso que o Banco Inter envia quando a cobrança muda de situação.
 * Cobrança desconhecida é ignorada. O mesmo aviso pode chegar mais de uma vez.
 */
class ReceptorDeWebhookCobranca
{
    public function __construct(private LancamentoRepositoryInterface $lancamentos) {}

    /**
     * @param  list<mixed>|array<string, mixed>  $corpo
     */
    public function receber(array $corpo): void
    {
        $itens = array_is_list($corpo) ? $corpo : [$corpo];

        foreach ($itens as $item) {
            if (! is_array($item)) {
                continue;
            }

            $this->aplicar($item);
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function aplicar(array $item): void
    {
        $codigo = is_string($item['codigoSolicitacao'] ?? null) ? trim($item['codigoSolicitacao']) : '';
        $situacao = is_string($item['situacao'] ?? null) ? $item['situacao'] : '';

        if ($codigo === '' || $situacao === '') {
            return;
        }

        $lancamento = $this->lancamentos->buscarPorCodigoSolicitacao($codigo)
            ?? $this->porSeuNumero($item, $codigo);

        if ($lancamento === null) {
            return;
        }

        if ($situacao === SituacaoCobranca::Recebido->value || $situacao === SituacaoCobranca::MarcadoRecebido->value) {
            $this->receberPagamento($lancamento, $item);
            $this->adiantarEnvio($lancamento);

            return;
        }

        if ($situacao === SituacaoCobranca::Cancelado->value && $lancamento->situacao === SituacaoLancamento::Aberto) {
            $this->lancamentos->cancelar($lancamento);

            return;
        }

        if (in_array($situacao, [
            SituacaoCobranca::AReceber->value,
            SituacaoCobranca::Atrasado->value,
            SituacaoCobranca::Protesto->value,
        ], true)) {
            $this->adiantarEnvio($lancamento);
        }
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function porSeuNumero(array $item, string $codigo): ?Lancamento
    {
        $seuNumero = $item['seuNumero'] ?? null;

        if (! is_string($seuNumero) || preg_match('/^L(\d{14})$/', $seuNumero, $partes) !== 1) {
            return null;
        }

        $lancamento = $this->lancamentos->buscarPorId((int) $partes[1]);

        if ($lancamento === null) {
            return null;
        }

        if ($lancamento->codigo_solicitacao !== null && $lancamento->codigo_solicitacao !== $codigo) {
            return null;
        }

        if ($lancamento->codigo_solicitacao === null) {
            $this->lancamentos->registrarSolicitacao($lancamento, $codigo);
            $lancamento->refresh();
        }

        return $lancamento;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function receberPagamento(Lancamento $lancamento, array $item): void
    {
        if ($lancamento->situacao === SituacaoLancamento::Pago) {
            return;
        }

        $this->lancamentos->marcarPago($lancamento, $this->instante($item['dataHoraSituacao'] ?? null));
    }

    private function instante(mixed $valor): CarbonImmutable
    {
        if (! is_string($valor) || trim($valor) === '') {
            return CarbonImmutable::now('UTC');
        }

        try {
            return CarbonImmutable::parse($valor)->utc();
        } catch (Throwable) {
            return CarbonImmutable::now('UTC');
        }
    }

    /**
     * O boleto já existe no banco. A fila baixa o PDF e envia o e-mail no próximo minuto.
     */
    private function adiantarEnvio(Lancamento $lancamento): void
    {
        if ($lancamento->codigo_solicitacao === null || $lancamento->email_enviado_em !== null) {
            return;
        }

        if (! in_array($lancamento->situacao, [SituacaoLancamento::Aberto, SituacaoLancamento::Pago], true)) {
            return;
        }

        $fila = FilaEmissao::query()->where('lancamento_id', $lancamento->id)->first();

        if ($fila?->reservado_em !== null && $fila->reservado_em->greaterThan(now()->subMinutes(2))) {
            return;
        }

        FilaEmissao::query()->updateOrCreate(
            ['lancamento_id' => $lancamento->id],
            [
                'disponivel_em' => now(),
                'prioridade' => true,
                'reservado_em' => null,
                'processado_em' => null,
            ],
        );
    }
}
