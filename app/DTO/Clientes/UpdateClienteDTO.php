<?php

namespace App\DTO\Clientes;

use App\Enums\TipoPessoa;
use App\Http\Requests\Painel\UpdateClienteRequest;
use App\Support\ListaDeEmails;

class UpdateClienteDTO
{
    public function __construct(
        public int $id,
        public string $nome,
        public string $email,
        public string $nome_contato,
        public ?TipoPessoa $tipo_pessoa = null,
        public ?string $cpf_cnpj = null,
        public ?string $cep = null,
        public ?string $endereco = null,
        public ?string $numero = null,
        public ?string $complemento = null,
        public ?string $bairro = null,
        public ?string $cidade = null,
        public ?string $uf = null,
        public ?string $emails_adicionais = null,
    ) {}

    public static function makeFromRequest(UpdateClienteRequest $request, int $id): self
    {
        $tipo = $request->validated('tipo_pessoa');

        return new self(
            $id,
            $request->validated('nome'),
            $request->validated('email'),
            $request->validated('nome_contato'),
            $tipo === null ? null : TipoPessoa::from($tipo),
            $request->validated('cpf_cnpj'),
            $request->validated('cep'),
            $request->validated('endereco'),
            $request->validated('numero'),
            $request->validated('complemento'),
            $request->validated('bairro'),
            $request->validated('cidade'),
            $request->validated('uf'),
            ListaDeEmails::gravar($request->validated('emails_adicionais'), $request->validated('email')),
        );
    }
}
