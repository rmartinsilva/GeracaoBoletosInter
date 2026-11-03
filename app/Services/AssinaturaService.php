<?php

namespace App\Services;

use App\DTO\Assinaturas\CreateAssinaturaDTO;
use App\DTO\Assinaturas\UpdateAssinaturaDTO;
use App\Enums\SituacaoAssinatura;
use App\Enums\SituacaoLancamento;
use App\Exceptions\RegraNegocioException;
use App\Models\Assinatura;
use App\Models\Lancamento;
use App\Repositories\Contracts\AssinaturaRepositoryInterface;
use App\Repositories\Contracts\LancamentoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class AssinaturaService
{
    public function __construct(
        protected AssinaturaRepositoryInterface $assinaturaRepository,
        protected LancamentoRepositoryInterface $lancamentoRepository,
        protected PlanejadorDeLancamentos $planejador,
        protected DataHoraService $dataHora,
    ) {}

    /**
     * @return Collection<int, Assinatura>
     */
    public function listarPorCliente(int $clienteId): Collection
    {
        return $this->assinaturaRepository->listarPorCliente($clienteId);
    }

    public function contarPorCliente(int $clienteId): int
    {
        return $this->assinaturaRepository->contarPorCliente($clienteId);
    }

    public function buscarPorId(int $id): ?stdClass
    {
        return $this->assinaturaRepository->buscarPorId($id);
    }

    public function buscarParaExibicao(int $clienteId, int $assinaturaId): ?Assinatura
    {
        return $this->assinaturaRepository->buscarParaExibicao($clienteId, $assinaturaId);
    }

    /**
     * Cadastra a assinatura e gera os lançamentos previstos até o mês atual.
     *
     * @return array{assinatura: stdClass, gerados: int, limitado: bool}
     */
    public function criar(CreateAssinaturaDTO $dto): array
    {
        return DB::transaction(function () use ($dto): array {
            $assinatura = $this->assinaturaRepository->criar($dto);
            $geracao = $this->gerarPrevistos((int) $assinatura->id);

            return [
                'assinatura' => $assinatura,
                'gerados' => $geracao['gerados'],
                'limitado' => $geracao['limitado'],
            ];
        });
    }

    /**
     * Atualiza a assinatura. Encerrar cancela lançamentos em aberto com vencimento posterior.
     * O valor dos lançamentos já gerados não muda.
     */
    public function atualizar(UpdateAssinaturaDTO $dto): ?stdClass
    {
        return DB::transaction(function () use ($dto): ?stdClass {
            $assinatura = $this->assinaturaRepository->atualizar($dto);

            if ($assinatura && $dto->situacao === SituacaoAssinatura::Encerrada && $dto->data_fim !== null) {
                $this->lancamentoRepository->cancelarAbertosComVencimentoApos($dto->id, $dto->data_fim);
            }

            return $assinatura;
        });
    }

    /**
     * Exclui a assinatura quando ela ainda não tem pagamento registrado.
     */
    public function deletar(int $id): bool
    {
        if (! $this->assinaturaRepository->buscarModelo($id)) {
            return false;
        }

        if ($this->lancamentoRepository->temPagos($id)) {
            throw new RegraNegocioException('Esta assinatura tem pagamentos registrados e não pode ser excluída. Encerre-a para manter o histórico.');
        }

        return $this->assinaturaRepository->deletar($id);
    }

    /**
     * Gera o lançamento do período seguinte.
     */
    public function gerarProximo(int $assinaturaId): Lancamento
    {
        $assinatura = $this->exigirModelo($assinaturaId);

        if ($assinatura->situacao !== SituacaoAssinatura::Ativa) {
            throw new RegraNegocioException('Assinatura encerrada não gera novos lançamentos.');
        }

        $periodo = $this->planejador->proximo(
            $assinatura->periodicidade,
            $this->lancamentoRepository->ultimaCompetencia($assinaturaId),
            $assinatura->data_inicio->toDateString(),
            $assinatura->data_fim?->toDateString(),
            $assinatura->dia_vencimento,
        );

        if ($this->lancamentoRepository->existe($assinaturaId, $periodo['competencia'])) {
            throw new RegraNegocioException('Esse lançamento já existe.');
        }

        return $this->lancamentoRepository->criar(
            $assinaturaId,
            $periodo['competencia'],
            (string) $assinatura->valor,
            $periodo['vencimento'],
        );
    }

    /**
     * Registra o pagamento de um lançamento em aberto.
     * $dataPagamento é um dia de calendário (Y-m-d) no horário de Brasília.
     */
    public function registrarPagamento(int $assinaturaId, int $lancamentoId, string $dataPagamento): void
    {
        $lancamento = $this->lancamentoAberto(
            $assinaturaId,
            $lancamentoId,
            'Só é possível registrar pagamento de um lançamento em aberto.',
        );

        $this->lancamentoRepository->marcarPago(
            $lancamento,
            $this->dataHora->paraUtc($dataPagamento.' 00:00:00'),
        );
    }

    /**
     * Cancela um lançamento em aberto. O registro permanece no histórico.
     */
    public function cancelarLancamento(int $assinaturaId, int $lancamentoId): void
    {
        $lancamento = $this->lancamentoAberto(
            $assinaturaId,
            $lancamentoId,
            'Só é possível cancelar um lançamento em aberto.',
        );

        $this->lancamentoRepository->cancelar($lancamento);
    }

    /**
     * Corrige o valor e o vencimento de um lançamento em aberto.
     */
    public function atualizarLancamento(int $assinaturaId, int $lancamentoId, string $valor, string $vencimento): void
    {
        $lancamento = $this->lancamentoAberto(
            $assinaturaId,
            $lancamentoId,
            'Só é possível ajustar um lançamento em aberto.',
        );

        $this->lancamentoRepository->atualizar($lancamento, $valor, $vencimento);
    }

    /**
     * @return array{gerados: int, limitado: bool}
     */
    private function gerarPrevistos(int $assinaturaId): array
    {
        $assinatura = $this->exigirModelo($assinaturaId);
        $planejado = $this->planejador->planejar(
            $assinatura->periodicidade,
            $assinatura->data_inicio->toDateString(),
            $assinatura->data_fim?->toDateString(),
            $assinatura->dia_vencimento,
            $this->dataHora->agora()->toDateString(),
        );

        $gerados = 0;

        foreach ($planejado['periodos'] as $periodo) {
            if ($this->lancamentoRepository->existe($assinaturaId, $periodo['competencia'])) {
                continue;
            }

            $this->lancamentoRepository->criar(
                $assinaturaId,
                $periodo['competencia'],
                (string) $assinatura->valor,
                $periodo['vencimento'],
            );
            $gerados++;
        }

        return [
            'gerados' => $gerados,
            'limitado' => $planejado['limitado'],
        ];
    }

    private function exigirModelo(int $id): Assinatura
    {
        $assinatura = $this->assinaturaRepository->buscarModelo($id);

        if (! $assinatura) {
            throw new RegraNegocioException('Assinatura não encontrada.');
        }

        return $assinatura;
    }

    private function lancamentoAberto(int $assinaturaId, int $lancamentoId, string $mensagem): Lancamento
    {
        $lancamento = $this->lancamentoRepository->buscarDaAssinatura($assinaturaId, $lancamentoId);

        if (! $lancamento) {
            throw new RegraNegocioException('Lançamento não encontrado.');
        }

        if ($lancamento->situacao !== SituacaoLancamento::Aberto) {
            throw new RegraNegocioException($mensagem);
        }

        return $lancamento;
    }
}
