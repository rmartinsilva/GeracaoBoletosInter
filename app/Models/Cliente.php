<?php

namespace App\Models;

use App\DTO\Inter\PagadorCobrancaDTO;
use App\Enums\Inter\TipoPessoa as TipoPessoaInter;
use App\Enums\TipoPessoa;
use App\Exceptions\RegraNegocioException;
use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'clientes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nome',
        'email',
        'nome_contato',
        'tipo_pessoa',
        'cpf_cnpj',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo_pessoa' => TipoPessoa::class,
        ];
    }

    /**
     * Assinaturas de cobrança deste cliente.
     */
    public function assinaturas(): HasMany
    {
        return $this->hasMany(Assinatura::class);
    }

    /**
     * Pagador exigido pela API de cobrança. Falha com uma mensagem clara se faltar dado.
     */
    public function pagadorDaCobranca(): PagadorCobrancaDTO
    {
        if ($this->tipo_pessoa === null || $this->cpf_cnpj === null || $this->endereco === null || $this->cidade === null || $this->uf === null || $this->cep === null) {
            throw new RegraNegocioException('O cliente '.$this->nome.' precisa de tipo de pessoa, CPF ou CNPJ, endereço, cidade, UF e CEP para emitir o boleto.');
        }

        return new PagadorCobrancaDTO(
            cpfCnpj: $this->cpf_cnpj,
            tipoPessoa: $this->tipo_pessoa === TipoPessoa::Juridica ? TipoPessoaInter::Juridica : TipoPessoaInter::Fisica,
            nome: $this->nome,
            endereco: $this->endereco,
            cidade: $this->cidade,
            uf: $this->uf,
            cep: $this->cep,
            numero: $this->numero,
            complemento: $this->complemento,
            bairro: $this->bairro,
            email: $this->email,
        );
    }
}
