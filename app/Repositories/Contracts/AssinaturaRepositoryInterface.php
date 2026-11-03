<?php

namespace App\Repositories\Contracts;

use App\DTO\Assinaturas\CreateAssinaturaDTO;
use App\DTO\Assinaturas\UpdateAssinaturaDTO;
use App\Models\Assinatura;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

interface AssinaturaRepositoryInterface
{
    /**
     * Assinaturas do cliente, ativas primeiro.
     *
     * @return Collection<int, Assinatura>
     */
    public function listarPorCliente(int $clienteId): Collection;

    /**
     * Assinaturas ainda em cobrança deste cliente.
     *
     * @return Collection<int, Assinatura>
     */
    public function listarAtivasDoCliente(int $clienteId): Collection;

    /**
     * Quantas assinaturas o cliente tem, em qualquer situação.
     */
    public function contarPorCliente(int $clienteId): int;

    /**
     * Busca uma assinatura por ID.
     */
    public function buscarPorId(int $id): ?stdClass;

    /**
     * Busca o model, para a geração de lançamentos.
     */
    public function buscarModelo(int $id): ?Assinatura;

    /**
     * Assinatura do cliente, com os lançamentos em ordem de competência.
     */
    public function buscarParaExibicao(int $clienteId, int $assinaturaId): ?Assinatura;

    /**
     * Cria uma nova assinatura.
     */
    public function criar(CreateAssinaturaDTO $dto): stdClass;

    /**
     * Atualiza uma assinatura existente. Retorna null se ela não existir.
     */
    public function atualizar(UpdateAssinaturaDTO $dto): ?stdClass;

    /**
     * Deleta uma assinatura. Retorna false se ela não existir.
     */
    public function deletar(int $id): bool;

    /**
     * Encerra a assinatura na data informada, sem alterar o valor nem os lançamentos.
     */
    public function encerrar(int $id, string $dataFim): void;

    /**
     * Assinaturas ativas, com o cliente, para a geração diária das cobranças.
     *
     * @return Collection<int, Assinatura>
     */
    public function listarAtivas(): Collection;
}
