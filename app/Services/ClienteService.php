<?php

namespace App\Services;

use App\DTO\Clientes\CreateClienteDTO;
use App\DTO\Clientes\UpdateClienteDTO;
use App\Enums\SituacaoCliente;
use App\Exceptions\RegraNegocioException;
use App\Repositories\Contracts\AssinaturaRepositoryInterface;
use App\Repositories\Contracts\ClienteRepositoryInterface;
use App\Repositories\PaginationPresenter;
use Illuminate\Support\Facades\DB;
use stdClass;

class ClienteService
{
    public function __construct(
        protected ClienteRepositoryInterface $clienteRepository,
        protected AssinaturaRepositoryInterface $assinaturaRepository,
        protected AssinaturaService $assinaturaService,
        protected DataHoraService $dataHora,
    ) {}

    /**
     * Lista os clientes paginados, com filtro opcional por nome, e-mail ou contato.
     */
    public function listarClientes(int $pagina = 1, int $porPagina = 15, ?string $filtro = null, ?SituacaoCliente $situacao = null): PaginationPresenter
    {
        return $this->clienteRepository->paginar(
            pagina: $pagina,
            porPagina: $porPagina,
            filtro: $filtro,
            situacao: $situacao,
        );
    }

    /**
     * Busca um cliente por ID.
     */
    public function buscarPorId(int $id): ?stdClass
    {
        return $this->clienteRepository->buscarPorId($id);
    }

    /**
     * Cadastra um novo cliente.
     */
    public function criarCliente(CreateClienteDTO $dto): stdClass
    {
        return $this->clienteRepository->criar($dto);
    }

    /**
     * Atualiza um cliente existente. Retorna null se ele não existir.
     */
    public function atualizarCliente(UpdateClienteDTO $dto): ?stdClass
    {
        return $this->clienteRepository->atualizar($dto);
    }

    /**
     * Deleta um cliente. Retorna false se ele não existir.
     */
    public function deletarCliente(int $id): bool
    {
        if (! $this->clienteRepository->buscarPorId($id)) {
            return false;
        }

        if ($this->assinaturaRepository->contarPorCliente($id) > 0) {
            throw new RegraNegocioException('Este cliente tem assinaturas e não pode ser excluído. O histórico de cobrança fica nas assinaturas.');
        }

        return $this->clienteRepository->deletar($id);
    }

    /**
     * Inativa o cliente e encerra as assinaturas ativas.
     * Lançamentos em aberto com vencimento posterior à data de hoje são cancelados.
     * Retorna quantas assinaturas foram encerradas, ou null se o cliente não existir.
     */
    public function inativar(int $id): ?int
    {
        $cliente = $this->clienteRepository->buscarModelo($id);

        if (! $cliente) {
            return null;
        }

        if ($cliente->situacao === SituacaoCliente::Inativo) {
            return 0;
        }

        return DB::transaction(function () use ($cliente): int {
            $hoje = $this->dataHora->agora()->toDateString();
            $encerradas = 0;

            foreach ($this->assinaturaRepository->listarAtivasDoCliente($cliente->id) as $assinatura) {
                $this->assinaturaService->encerrar($assinatura->id, $hoje);
                $encerradas++;
            }

            $this->clienteRepository->definirSituacao($cliente->id, SituacaoCliente::Inativo);

            return $encerradas;
        });
    }

    /**
     * Volta o cliente para ativo. Assinaturas já encerradas permanecem encerradas.
     */
    public function reativar(int $id): bool
    {
        if (! $this->clienteRepository->buscarModelo($id)) {
            return false;
        }

        $this->clienteRepository->definirSituacao($id, SituacaoCliente::Ativo);

        return true;
    }
}
