<?php

namespace App\DTO\Inter;

use App\Enums\Inter\FormaRecebimento;
use App\Services\Inter\ExcecaoInter;
use App\Services\Inter\PayloadInter;
use App\Services\Inter\ValorInter;

class EmitirCobrancaDTO
{
    public readonly string $seuNumero;

    public readonly float $valorNominal;

    public readonly string $dataVencimento;

    /**
     * @var list<string>|null
     */
    public readonly ?array $formasRecebimento;

    /**
     * @param  list<FormaRecebimento>|null  $formasRecebimento
     */
    public function __construct(
        string $seuNumero,
        float|int|string $valorNominal,
        string $dataVencimento,
        public readonly int $numDiasAgenda,
        public readonly PagadorCobrancaDTO $pagador,
        public readonly ?DescontoCobrancaDTO $desconto = null,
        public readonly ?MultaCobrancaDTO $multa = null,
        public readonly ?MoraCobrancaDTO $mora = null,
        public readonly ?MensagemCobrancaDTO $mensagem = null,
        public readonly ?BeneficiarioFinalDTO $beneficiarioFinal = null,
        ?array $formasRecebimento = null,
        public readonly ?NotaFiscalCobrancaDTO $notaFiscal = null,
    ) {
        $this->seuNumero = ValorInter::textoObrigatorio($seuNumero, 'Informe o seu número da cobrança.');

        if (preg_match('/\s/', $this->seuNumero) === 1 || mb_strlen($this->seuNumero) > 15) {
            throw new ExcecaoInter('O seu número da cobrança deve ter no máximo 15 caracteres, sem espaços.');
        }

        $this->valorNominal = ValorInter::dinheiro($valorNominal, 'valor nominal', faixaCobranca: true);
        $this->dataVencimento = ValorInter::data($dataVencimento, 'data de vencimento');

        if ($this->numDiasAgenda < 0 || $this->numDiasAgenda > 60) {
            throw new ExcecaoInter('O número de dias de agenda deve estar entre 0 e 60.');
        }

        $this->formasRecebimento = self::normalizarFormas($formasRecebimento);
    }

    /**
     * @return array<string, mixed>
     */
    public function paraPayload(): array
    {
        return PayloadInter::semNulos([
            'seuNumero' => $this->seuNumero,
            'valorNominal' => $this->valorNominal,
            'dataVencimento' => $this->dataVencimento,
            'numDiasAgenda' => $this->numDiasAgenda,
            'pagador' => $this->pagador->paraPayload(),
            'desconto' => $this->desconto?->paraPayload(),
            'multa' => $this->multa?->paraPayload(),
            'mora' => $this->mora?->paraPayload(),
            'mensagem' => $this->mensagem?->paraPayload(),
            'beneficiarioFinal' => $this->beneficiarioFinal?->paraPayload(),
            'formasRecebimento' => $this->formasRecebimento,
            'notaFiscal' => $this->notaFiscal?->paraPayload(),
        ]);
    }

    /**
     * @param  list<FormaRecebimento>|null  $formas
     * @return list<string>|null
     */
    private static function normalizarFormas(?array $formas): ?array
    {
        if ($formas === null) {
            return null;
        }

        $valores = [];

        foreach ($formas as $forma) {
            if (! $forma instanceof FormaRecebimento) {
                throw new ExcecaoInter('A forma de recebimento informada é inválida.');
            }

            $valores[] = $forma->value;
        }

        $valores = array_values(array_unique($valores));

        if ($valores === []) {
            throw new ExcecaoInter('Informe ao menos uma forma de recebimento.');
        }

        if (in_array(FormaRecebimento::SemFormaPagamento->value, $valores, true) && count($valores) > 1) {
            throw new ExcecaoInter('A cobrança sem forma de pagamento não pode ser combinada com boleto ou Pix.');
        }

        if ($valores === [FormaRecebimento::Pix->value]) {
            throw new ExcecaoInter('A API de cobrança não aceita somente Pix. Informe boleto e Pix, ou use a API Pix para cobrança com vencimento.');
        }

        return $valores;
    }
}
