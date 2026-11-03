<?php

namespace App\Services;

use App\Enums\SituacaoCliente;
use App\Exceptions\RegraNegocioException;
use App\Models\Lancamento;
use App\Repositories\Contracts\ClienteRepositoryInterface;
use App\Repositories\Contracts\LancamentoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CobrancaAvulsaService
{
    public function __construct(
        private ClienteRepositoryInterface $clientes,
        private LancamentoRepositoryInterface $lancamentos,
    ) {}

    /**
     * @return Collection<int, Lancamento>
     */
    public function listar(int $clienteId): Collection
    {
        return $this->lancamentos->listarAvulsas($clienteId);
    }

    /**
     * Grava a cobrança e pede o boleto na hora.
     *
     * @return array{emitidas: int, enviadas: int, aguardando: int, falhas: int}
     */
    public function criar(int $clienteId, string $descricao, string $valor, string $vencimento): array
    {
        $cliente = $this->clientes->buscarPorId($clienteId);

        if ($cliente === null) {
            throw new RegraNegocioException('Cliente não encontrado.');
        }

        if (($cliente->situacao ?? SituacaoCliente::Ativo->value) !== SituacaoCliente::Ativo->value) {
            throw new RegraNegocioException('Cliente inativo não recebe cobrança nova. Reative o cliente antes.');
        }

        $lancamento = $this->lancamentos->criarAvulsa($clienteId, $descricao, $valor, $vencimento);

        return app(EmissorDeCobrancas::class)->emitirLancamento($lancamento);
    }
}
