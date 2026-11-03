<?php

namespace App\DTO\Inter;

use App\Enums\Inter\TipoPessoa;
use App\Services\Inter\ExcecaoInter;
use App\Services\Inter\PayloadInter;
use App\Services\Inter\ValorInter;

class BeneficiarioFinalDTO
{
    public readonly string $cpfCnpj;

    public readonly string $nome;

    public readonly string $endereco;

    public readonly string $cidade;

    public readonly string $uf;

    public readonly string $cep;

    public readonly ?string $bairro;

    public function __construct(
        string $cpfCnpj,
        public readonly TipoPessoa $tipoPessoa,
        string $nome,
        string $endereco,
        string $cidade,
        string $uf,
        string $cep,
        ?string $bairro = null,
    ) {
        $this->cpfCnpj = ValorInter::digitos($cpfCnpj);
        $esperado = $this->tipoPessoa === TipoPessoa::Fisica ? 11 : 14;

        if (strlen($this->cpfCnpj) !== $esperado) {
            throw new ExcecaoInter('O CPF ou CNPJ do beneficiário final é inválido.');
        }

        $this->nome = ValorInter::textoObrigatorio($nome, 'Informe o nome do beneficiário final.');
        $this->endereco = ValorInter::textoObrigatorio($endereco, 'Informe o endereço do beneficiário final.');
        $this->cidade = ValorInter::textoObrigatorio($cidade, 'Informe a cidade do beneficiário final.');
        $this->uf = strtoupper(ValorInter::textoObrigatorio($uf, 'Informe a UF do beneficiário final.'));

        if (! preg_match('/^[A-Z]{2}$/', $this->uf)) {
            throw new ExcecaoInter('A UF do beneficiário final deve ter duas letras.');
        }

        $this->cep = ValorInter::digitos($cep);

        if (strlen($this->cep) !== 8) {
            throw new ExcecaoInter('O CEP do beneficiário final deve ter 8 dígitos.');
        }

        $this->bairro = ValorInter::textoOpcional($bairro);
    }

    /**
     * @return array<string, string>
     */
    public function paraPayload(): array
    {
        return PayloadInter::semNulos([
            'cpfCnpj' => $this->cpfCnpj,
            'tipoPessoa' => $this->tipoPessoa->value,
            'nome' => $this->nome,
            'endereco' => $this->endereco,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'uf' => $this->uf,
            'cep' => $this->cep,
        ]);
    }
}
