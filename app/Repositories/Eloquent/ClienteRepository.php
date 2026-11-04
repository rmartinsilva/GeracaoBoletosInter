<?php

namespace App\Repositories\Eloquent;

use App\DTO\Clientes\CreateClienteDTO;
use App\DTO\Clientes\UpdateClienteDTO;
use App\Enums\SituacaoAssinatura;
use App\Enums\SituacaoCliente;
use App\Models\Cliente;
use App\Repositories\Contracts\ClienteRepositoryInterface;
use App\Repositories\PaginationPresenter;
use stdClass;

class ClienteRepository implements ClienteRepositoryInterface
{
    public function __construct(
        protected Cliente $model
    ) {}

    public function paginar(int $pagina = 1, int $porPagina = 15, ?string $filtro = null, ?SituacaoCliente $situacao = null): PaginationPresenter
    {
        $paginator = $this->model
            ->when($filtro, function ($query) use ($filtro) {
                $query->where(function ($query) use ($filtro) {
                    $query->where('nome', 'like', "%{$filtro}%")
                        ->orWhere('email', 'like', "%{$filtro}%")
                        ->orWhere('emails_adicionais', 'like', "%{$filtro}%")
                        ->orWhere('nome_contato', 'like', "%{$filtro}%");
                });
            })
            ->when($situacao, function ($query) use ($situacao) {
                $query->where('situacao', $situacao);
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

    public function buscarModelo(int $id): ?Cliente
    {
        return $this->model->newQuery()->find($id);
    }

    public function buscarPorCpfCnpj(string $cpfCnpj): ?stdClass
    {
        $cliente = $this->model->where('cpf_cnpj', $cpfCnpj)->first();

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

    public function definirSituacao(int $id, SituacaoCliente $situacao): void
    {
        $cliente = $this->model->newQuery()->find($id);

        if (! $cliente) {
            return;
        }

        $cliente->update(['situacao' => $situacao]);
    }
}
