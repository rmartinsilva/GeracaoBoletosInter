<?php

namespace App\Repositories\Contracts;

use App\DTO\Clientes\CreateClienteDTO;
use App\DTO\Clientes\UpdateClienteDTO;
use App\Repositories\PaginationPresenter;
use stdClass;

interface ClienteRepositoryInterface
{
    /**
     * Retorna os clientes paginados, filtrando por nome, e-mail ou contato.
     */
    public function paginar(int $pagina = 1, int $porPagina = 15, ?string $filtro = null): PaginationPresenter;

    /**
     * Busca um cliente por ID.
     */
    public function buscarPorId(int $id): ?stdClass;

    /**
     * Cria um novo cliente.
     */
    public function criar(CreateClienteDTO $dto): stdClass;

    /**
     * Atualiza um cliente existente. Retorna null se ele não existir.
     */
    public function atualizar(UpdateClienteDTO $dto): ?stdClass;

    /**
     * Deleta um cliente. Retorna false se ele não existir.
     */
    public function deletar(int $id): bool;
}
