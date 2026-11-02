<?php

namespace App\Services;

use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Repositories\PaginationPresenter;
use App\Models\Usuario;

class UsuarioService
{
    public function __construct(
        protected UsuarioRepositoryInterface $usuarioRepository
    ) {
    }

    /**
     * Lista todos os usuários paginados.
     *
     * @param int $perPage
     * @return PaginationPresenter
     */
    public function listarUsuarios(int $perPage = 15): PaginationPresenter
    {
        return $this->usuarioRepository->getAllPaginated($perPage);
    }

    /**
     * Busca usuários por termo de pesquisa.
     *
     * @param string $search
     * @param int $perPage
     * @return PaginationPresenter
     */
    public function buscarUsuarios(string $search, int $perPage = 15): PaginationPresenter
    {
        return $this->usuarioRepository->searchPaginated($search, $perPage);
    }

    /**
     * Busca um usuário por ID.
     *
     * @param int $id
     * @return Usuario|null
     */
    public function buscarPorId(int $id): ?Usuario
    {
        return $this->usuarioRepository->findByIdWithGrupos($id);
    }

    /**
     * Cria um novo usuário e vincula aos grupos.
     *
     * @param array $data
     * @param array $grupoIds
     * @return Usuario
     */
    public function criarUsuario(array $data, array $grupoIds = []): Usuario
    {
        // Validar dados essenciais
        if (empty($data['name']) || empty($data['login']) || empty($data['password'])) {
            throw new \InvalidArgumentException('Nome, login e senha são obrigatórios.');
        }

        // Criar usuário
        $usuario = $this->usuarioRepository->create([
            'name' => $data['name'],
            'login' => $data['login'],
            'password' => $data['password'],
        ]);

        // Vincular grupos
        if (!empty($grupoIds)) {
            $this->usuarioRepository->syncGrupos($usuario->id, $grupoIds);
        }

        return $usuario;
    }

    /**
     * Atualiza um usuário existente.
     *
     * @param int $id
     * @param array $data
     * @param array $grupoIds
     * @return bool
     */
    public function atualizarUsuario(int $id, array $data, array $grupoIds = []): bool
    {
        // Preparar dados para atualização
        $updateData = [
            'name' => $data['name'] ?? null,
            'login' => $data['login'] ?? null,
        ];

        // Adicionar senha apenas se foi fornecida
        if (!empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        // Remover valores nulos
        $updateData = array_filter($updateData, fn($value) => $value !== null);

        // Atualizar usuário
        $updated = $this->usuarioRepository->update($id, $updateData);

        // Sincronizar grupos
        if ($updated && isset($grupoIds)) {
            $this->usuarioRepository->syncGrupos($id, $grupoIds);
        }

        return $updated;
    }

    /**
     * Deleta um usuário.
     *
     * @param int $id
     * @return bool
     */
    public function deletarUsuario(int $id): bool
    {
        return $this->usuarioRepository->delete($id);
    }

    /**
     * Verifica se um login já está em uso.
     *
     * @param string $login
     * @param int|null $excludeId
     * @return bool
     */
    public function loginExiste(string $login, ?int $excludeId = null): bool
    {
        $usuarios = $this->usuarioRepository->getAllWithGrupos();

        foreach ($usuarios as $usuario) {
            if ($usuario->login === $login && $usuario->id !== $excludeId) {
                return true;
            }
        }

        return false;
    }
}
