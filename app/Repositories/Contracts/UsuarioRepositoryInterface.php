<?php

namespace App\Repositories\Contracts;

use App\Models\Usuario;
use App\Repositories\PaginationPresenter;

interface UsuarioRepositoryInterface
{
    /**
     * Retorna todos os usuários paginados.
     *
     * @param int $perPage
     * @return PaginationPresenter
     */
    public function getAllPaginated(int $perPage = 15): PaginationPresenter;

    /**
     * Retorna todos os usuários com seus grupos eager loaded.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithGrupos();

    /**
     * Busca um usuário por ID com grupos eager loaded.
     *
     * @param int $id
     * @return Usuario|null
     */
    public function findByIdWithGrupos(int $id): ?Usuario;

    /**
     * Busca um usuário por ID.
     *
     * @param int $id
     * @return Usuario|null
     */
    public function findById(int $id): ?Usuario;

    /**
     * Cria um novo usuário.
     *
     * @param array $data
     * @return Usuario
     */
    public function create(array $data): Usuario;

    /**
     * Atualiza um usuário existente.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Deleta um usuário.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Sincroniza os grupos do usuário.
     *
     * @param int $usuarioId
     * @param array $grupoIds
     * @return void
     */
    public function syncGrupos(int $usuarioId, array $grupoIds): void;

    /**
     * Busca usuários por termo de busca (nome ou login).
     *
     * @param string $search
     * @param int $perPage
     * @return PaginationPresenter
     */
    public function searchPaginated(string $search, int $perPage = 15): PaginationPresenter;
}
