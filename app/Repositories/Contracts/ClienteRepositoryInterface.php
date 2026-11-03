<?php

namespace App\Repositories\Contracts;

use App\DTO\Clientes\CreateClienteDTO;
use App\DTO\Clientes\UpdateClienteDTO;
use App\Enums\SituacaoCliente;
use App\Models\Cliente;
use App\Repositories\PaginationPresenter;
use stdClass;

interface ClienteRepositoryInterface
{
    /**
     * Retorna os clientes paginados, filtrando por nome, e-mail ou contato.
     */
    public function paginar(int $pagina = 1, int $porPagina = 15, ?string $filtro = null, ?SituacaoCliente $situacao = null): PaginationPresenter;

    /**
     * Busca um cliente por ID.
     */
    public function buscarPorId(int $id): ?stdClass;

    /**
     * Busca o model, para mudar a situação.
     */
    public function buscarModelo(int $id): ?Cliente;

    /**
     * Busca um cliente pelo CPF ou CNPJ, só com dígitos.
     */
    public function buscarPorCpfCnpj(string $cpfCnpj): ?stdClass;

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

    /**
     * Define se o cliente continua sendo cobrado.
     */
    public function definirSituacao(int $id, SituacaoCliente $situacao): void;
}
