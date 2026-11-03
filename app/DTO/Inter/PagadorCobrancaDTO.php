<?php

namespace App\DTO\Inter;

use App\Enums\Inter\TipoPessoa;
use App\Services\Inter\ExcecaoInter;
use App\Services\Inter\PayloadInter;
use App\Services\Inter\ValorInter;

class PagadorCobrancaDTO
{
    public readonly string $cpfCnpj;

    public readonly string $nome;

    public readonly string $endereco;

    public readonly string $cidade;

    public readonly string $uf;

    public readonly string $cep;

    public readonly ?string $numero;

    public readonly ?string $complemento;

    public readonly ?string $bairro;

    public readonly ?string $email;

    public readonly ?string $ddd;

    public readonly ?string $telefone;

    public function __construct(
        string $cpfCnpj,
        public readonly TipoPessoa $tipoPessoa,
        string $nome,
        string $endereco,
        string $cidade,
        string $uf,
        string $cep,
        ?string $numero = null,
        ?string $complemento = null,
        ?string $bairro = null,
        ?string $email = null,
        ?string $ddd = null,
        ?string $telefone = null,
    ) {
        $this->cpfCnpj = ValorInter::digitos($cpfCnpj);
        $esperado = $this->tipoPessoa === TipoPessoa::Fisica ? 11 : 14;

        if (strlen($this->cpfCnpj) !== $esperado) {
            $documento = $this->tipoPessoa === TipoPessoa::Fisica ? 'CPF' : 'CNPJ';

            throw new ExcecaoInter("O {$documento} do pagador deve ter {$esperado} dígitos.");
        }

        $this->nome = ValorInter::textoObrigatorio($nome, 'Informe o nome do pagador.');
        $this->endereco = ValorInter::textoObrigatorio($endereco, 'Informe o endereço do pagador.');
        $this->cidade = ValorInter::textoObrigatorio($cidade, 'Informe a cidade do pagador.');
        $this->uf = strtoupper(ValorInter::textoObrigatorio($uf, 'Informe a UF do pagador.'));

        if (! preg_match('/^[A-Z]{2}$/', $this->uf)) {
            throw new ExcecaoInter('A UF do pagador deve ter duas letras.');
        }

        $this->cep = ValorInter::digitos($cep);

        if (strlen($this->cep) !== 8) {
            throw new ExcecaoInter('O CEP do pagador deve ter 8 dígitos.');
        }

        $this->numero = ValorInter::textoOpcional($numero);
        $this->complemento = ValorInter::textoOpcional($complemento);
        $this->bairro = ValorInter::textoOpcional($bairro);
        $this->email = ValorInter::textoOpcional($email);

        if ($this->email !== null && filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ExcecaoInter('O e-mail do pagador é inválido.');
        }

        $this->ddd = $this->digitosOpcionais($ddd, 'O DDD do pagador deve ter 2 dígitos.', 2);
        $this->telefone = $this->digitosOpcionais($telefone, 'O telefone do pagador deve ter de 8 a 9 dígitos.', null);
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
            'numero' => $this->numero,
            'complemento' => $this->complemento,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'uf' => $this->uf,
            'cep' => $this->cep,
            'email' => $this->email,
            'ddd' => $this->ddd,
            'telefone' => $this->telefone,
        ]);
    }

    private function digitosOpcionais(?string $valor, string $mensagem, ?int $tamanhoExato): ?string
    {
        $texto = ValorInter::textoOpcional($valor);

        if ($texto === null) {
            return null;
        }

        $digitos = ValorInter::digitos($texto);

        if ($digitos === '') {
            throw new ExcecaoInter($mensagem);
        }

        if ($tamanhoExato !== null && strlen($digitos) !== $tamanhoExato) {
            throw new ExcecaoInter($mensagem);
        }

        if ($tamanhoExato === null && (strlen($digitos) < 8 || strlen($digitos) > 9)) {
            throw new ExcecaoInter($mensagem);
        }

        return $digitos;
    }
}
