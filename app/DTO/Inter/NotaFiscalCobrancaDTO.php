<?php

namespace App\DTO\Inter;

use App\Services\Inter\ExcecaoInter;
use App\Services\Inter\PayloadInter;
use App\Services\Inter\ValorInter;

class NotaFiscalCobrancaDTO
{
    public readonly string $chaveNFe;

    public readonly string $dataEmissao;

    public readonly string $naturezaOperacao;

    public function __construct(
        string $chaveNFe,
        public readonly int $numero,
        public readonly int $serie,
        string $dataEmissao,
        public readonly int $parcela,
        string $naturezaOperacao,
    ) {
        $this->chaveNFe = ValorInter::digitos($chaveNFe);

        if (strlen($this->chaveNFe) !== 44) {
            throw new ExcecaoInter('A chave da nota fiscal deve ter 44 dígitos.');
        }

        if ($this->numero < 1) {
            throw new ExcecaoInter('O número da nota fiscal deve ser maior que zero.');
        }

        if ($this->serie < 0) {
            throw new ExcecaoInter('A série da nota fiscal não pode ser negativa.');
        }

        if ($this->parcela < 1) {
            throw new ExcecaoInter('A parcela da nota fiscal deve ser maior que zero.');
        }

        $this->dataEmissao = ValorInter::data($dataEmissao, 'data de emissão da nota fiscal');
        $this->naturezaOperacao = ValorInter::textoObrigatorio(
            $naturezaOperacao,
            'Informe a natureza da operação da nota fiscal.',
        );
    }

    /**
     * @return array<string, int|string>
     */
    public function paraPayload(): array
    {
        return PayloadInter::semNulos([
            'chaveNFe' => $this->chaveNFe,
            'numero' => $this->numero,
            'serie' => $this->serie,
            'dataEmissao' => $this->dataEmissao,
            'parcela' => $this->parcela,
            'naturezaOperacao' => $this->naturezaOperacao,
        ]);
    }
}
