<?php

namespace App\Repositories\Eloquent;

use App\DTO\Assinaturas\CreateAssinaturaDTO;
use App\DTO\Assinaturas\UpdateAssinaturaDTO;
use App\Enums\SituacaoAssinatura;
use App\Models\Assinatura;
use App\Repositories\Contracts\AssinaturaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

class AssinaturaRepository implements AssinaturaRepositoryInterface
{
    public function __construct(
        protected Assinatura $model
    ) {}

    public function listarPorCliente(int $clienteId): Collection
    {
        return $this->model->newQuery()
            ->where('cliente_id', $clienteId)
            ->withCount('lancamentos')
            ->orderBy('situacao')
            ->orderByDesc('data_inicio')
            ->get();
    }

    public function contarPorCliente(int $clienteId): int
    {
        return $this->model->newQuery()
            ->where('cliente_id', $clienteId)
            ->count();
    }

    public function buscarPorId(int $id): ?stdClass
    {
        $assinatura = $this->model->newQuery()->find($id);

        if (! $assinatura) {
            return null;
        }

        return (object) $assinatura->toArray();
    }

    public function buscarModelo(int $id): ?Assinatura
    {
        return $this->model->newQuery()->find($id);
    }

    public function buscarParaExibicao(int $clienteId, int $assinaturaId): ?Assinatura
    {
        return $this->model->newQuery()
            ->where('cliente_id', $clienteId)
            ->whereKey($assinaturaId)
            ->with([
                'cliente',
                'lancamentos' => fn ($query) => $query->orderBy('competencia'),
            ])
            ->first();
    }

    public function criar(CreateAssinaturaDTO $dto): stdClass
    {
        $assinatura = $this->model->newQuery()->create([
            ...(array) $dto,
            'situacao' => 'ativa',
        ]);

        return (object) $assinatura->toArray();
    }

    public function atualizar(UpdateAssinaturaDTO $dto): ?stdClass
    {
        $assinatura = $this->model->newQuery()->find($dto->id);

        if (! $assinatura) {
            return null;
        }

        $dados = (array) $dto;
        unset($dados['id']);

        $assinatura->update($dados);

        return (object) $assinatura->fresh()->toArray();
    }

    public function deletar(int $id): bool
    {
        $assinatura = $this->model->newQuery()->find($id);

        if (! $assinatura) {
            return false;
        }

        return (bool) $assinatura->delete();
    }

    public function listarAtivas(): Collection
    {
        return $this->model->newQuery()
            ->where('situacao', SituacaoAssinatura::Ativa)
            ->with('cliente')
            ->orderBy('id')
            ->get();
    }
}
