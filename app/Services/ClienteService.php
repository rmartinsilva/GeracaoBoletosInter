<?php

namespace App\Services;

use App\DTO\Clientes\CreateClienteDTO;
use App\DTO\Clientes\UpdateClienteDTO;
use App\Exceptions\RegraNegocioException;
use App\Repositories\Contracts\AssinaturaRepositoryInterface;
use App\Repositories\Contracts\ClienteRepositoryInterface;
use App\Repositories\PaginationPresenter;
use stdClass;

class ClienteService
{
    public function __construct(
        protected ClienteRepositoryInterface $clienteRepository,
        protected AssinaturaRepositoryInterface $assinaturaRepository,
    ) {}

    /**
     * Lista os clientes paginados, com filtro opcional por nome, e-mail ou contato.
     */
    public function listarClientes(int $pagina = 1, int $porPagina = 15, ?string $filtro = null): PaginationPresenter
    {
        return $this->clienteRepository->paginar(
            pagina: $pagina,
            porPagina: $porPagina,
            filtro: $filtro,
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
}
