<?php

namespace App\DTO\Inter;

use App\Enums\Inter\FiltrarDataPor;
use App\Enums\Inter\OrdenarCobrancasPor;
use App\Enums\Inter\SituacaoCobranca;
use App\Enums\Inter\TipoCobranca;
use App\Enums\Inter\TipoOrdenacao;
use App\Services\Inter\ExcecaoInter;
use App\Services\Inter\PayloadInter;
use App\Services\Inter\ValorInter;

class FiltroCobrancasDTO
{
    public readonly string $dataInicial;

    public readonly string $dataFinal;

    public readonly ?string $pessoaPagadora;

    public readonly ?string $cpfCnpjPessoaPagadora;

    public readonly ?string $seuNumero;

    public function __construct(
        string $dataInicial,
        string $dataFinal,
        public readonly FiltrarDataPor $filtrarDataPor = FiltrarDataPor::Vencimento,
        public readonly ?SituacaoCobranca $situacao = null,
        ?string $pessoaPagadora = null,
        ?string $cpfCnpjPessoaPagadora = null,
        ?string $seuNumero = null,
        public readonly ?TipoCobranca $tipoCobranca = null,
        public readonly int $itensPorPagina = 100,
        public readonly int $paginaAtual = 0,
        public readonly ?OrdenarCobrancasPor $ordenarPor = null,
        public readonly ?TipoOrdenacao $tipoOrdenacao = null,
    ) {
        $this->dataInicial = ValorInter::data($dataInicial, 'data inicial');
        $this->dataFinal = ValorInter::data($dataFinal, 'data final');

        if ($this->dataInicial > $this->dataFinal) {
            throw new ExcecaoInter('A data inicial não pode ser posterior à data final.');
        }

        $this->pessoaPagadora = ValorInter::textoOpcional($pessoaPagadora);
        $documento = ValorInter::textoOpcional($cpfCnpjPessoaPagadora);
        $this->cpfCnpjPessoaPagadora = $documento === null ? null : ValorInter::digitos($documento);

        if ($this->cpfCnpjPessoaPagadora !== null && ! in_array(strlen($this->cpfCnpjPessoaPagadora), [11, 14], true)) {
            throw new ExcecaoInter('O CPF ou CNPJ do filtro deve ter 11 ou 14 dígitos.');
        }

        $this->seuNumero = ValorInter::textoOpcional($seuNumero);

        if ($this->seuNumero !== null && mb_strlen($this->seuNumero) > 15) {
            throw new ExcecaoInter('O seu número do filtro deve ter no máximo 15 caracteres.');
        }

        if ($this->itensPorPagina < 1 || $this->itensPorPagina > 1000) {
            throw new ExcecaoInter('A página de cobranças deve ter de 1 a 1000 itens.');
        }

        if ($this->paginaAtual < 0) {
            throw new ExcecaoInter('A página atual não pode ser negativa.');
        }
    }

    /**
     * @return array<string, int|string>
     */
    public function paraConsulta(bool $sumario = false): array
    {
        $consulta = [
            'dataInicial' => $this->dataInicial,
            'dataFinal' => $this->dataFinal,
            'filtrarDataPor' => $this->filtrarDataPor->value,
            'situacao' => $this->situacao?->value,
            'pessoaPagadora' => $this->pessoaPagadora,
            'cpfCnpjPessoaPagadora' => $this->cpfCnpjPessoaPagadora,
            'seuNumero' => $this->seuNumero,
            'tipoCobranca' => $this->tipoCobranca?->value,
        ];

        if (! $sumario) {
            $consulta['paginacao.itensPorPagina'] = $this->itensPorPagina;
            $consulta['paginacao.paginaAtual'] = $this->paginaAtual;
            $consulta['ordenarPor'] = $this->ordenarPor?->value;
            $consulta['tipoOrdenacao'] = $this->tipoOrdenacao?->value;
        }

        return PayloadInter::semNulos($consulta);
    }
}
