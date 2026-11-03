<?php

namespace App\Repositories\Eloquent;

use App\DTO\Clientes\CreateClienteDTO;
use App\DTO\Clientes\UpdateClienteDTO;
use App\Enums\SituacaoAssinatura;
use App\Models\Cliente;
use App\Repositories\Contracts\ClienteRepositoryInterface;
use App\Repositories\PaginationPresenter;
use stdClass;

class ClienteRepository implements ClienteRepositoryInterface
{
    public function __construct(
        protected Cliente $model
    ) {}

    public function paginar(int $pagina = 1, int $porPagina = 15, ?string $filtro = null): PaginationPresenter
    {
        $paginator = $this->model
            ->when($filtro, function ($query) use ($filtro) {
                $query->where(function ($query) use ($filtro) {
                    $query->where('nome', 'like', "%{$filtro}%")
                        ->orWhere('email', 'like', "%{$filtro}%")
                        ->orWhere('nome_contato', 'like', "%{$filtro}%");
                });
            })
            ->withCount([
                'assinaturas as assinaturas_ativas' => function ($query): void {
                    $query->where('situacao', SituacaoAssinatura::Ativa);
                },
            ])
            ->orderBy('nome', 'asc')
            ->paginate($porPagina, ['*'], 'page', $pagina);

        return new PaginationPresenter($paginator);
    }

    public function buscarPorId(int $id): ?stdClass
    {
        $cliente = $this->model->find($id);

        if (! $cliente) {
            return null;
        }

        return (object) $cliente->toArray();
    }

    public function criar(CreateClienteDTO $dto): stdClass
    {
        $cliente = $this->model->create((array) $dto);

        return (object) $cliente->toArray();
    }

    public function atualizar(UpdateClienteDTO $dto): ?stdClass
    {
        $cliente = $this->model->find($dto->id);

        if (! $cliente) {
            return null;
        }

        $dados = (array) $dto;
        unset($dados['id']);

        $cliente->update($dados);

        return (object) $cliente->toArray();
    }

    public function deletar(int $id): bool
    {
        $cliente = $this->model->find($id);

        if (! $cliente) {
            return false;
        }

        return (bool) $cliente->delete();
    }
}
