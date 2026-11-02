<?php

namespace App\Repositories\Eloquent;

use App\Models\Usuario;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Repositories\PaginationPresenter;
use Illuminate\Support\Facades\Hash;

class UsuarioRepository implements UsuarioRepositoryInterface
{
    public function __construct(
        protected Usuario $model
    ) {
    }

    public function getAllPaginated(int $perPage = 15): PaginationPresenter
    {
        $paginator = $this->model
            ->with('grupos')
            ->orderBy('name', 'asc')
            ->paginate($perPage);

        return new PaginationPresenter($paginator);
    }

    public function getAllWithGrupos()
    {
        return $this->model
            ->with('grupos')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function findByIdWithGrupos(int $id): ?Usuario
    {
        return $this->model
            ->with('grupos')
            ->find($id);
    }

    public function findById(int $id): ?Usuario
    {
        return $this->model->find($id);
    }

    public function create(array $data): Usuario
    {
        // Hash da senha
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $usuario = $this->findById($id);

        if (!$usuario) {
            return false;
        }

        // Hash da senha apenas se foi fornecida
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $usuario->update($data);
    }

    public function delete(int $id): bool
    {
        $usuario = $this->findById($id);

        if (!$usuario) {
            return false;
        }

        // Remove os vínculos com grupos antes de deletar
        $usuario->grupos()->detach();

        return $usuario->delete();
    }

    public function syncGrupos(int $usuarioId, array $grupoIds): void
    {
        $usuario = $this->findById($usuarioId);

        if ($usuario) {
            $usuario->grupos()->sync($grupoIds);
        }
    }

    public function searchPaginated(string $search, int $perPage = 15): PaginationPresenter
    {
        $paginator = $this->model
            ->with('grupos')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('login', 'like', "%{$search}%");
            })
            ->orderBy('name', 'asc')
            ->paginate($perPage);

        return new PaginationPresenter($paginator);
    }
}
