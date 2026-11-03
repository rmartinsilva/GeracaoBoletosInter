<?php

namespace App\Repositories\Contracts;

use App\Models\Lancamento;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;

interface LancamentoRepositoryInterface
{
    /**
     * Há um lançamento desta assinatura na competência informada (Y-m-d).
     */
    public function existe(int $assinaturaId, string $competencia): bool;

    /**
     * Competência mais recente da assinatura, ou null se ainda não houver lançamentos.
     */
    public function ultimaCompetencia(int $assinaturaId): ?string;

    /**
     * Cria um lançamento em aberto, com o valor informado naquele momento.
     */
    public function criar(int $assinaturaId, string $competencia, string $valor, string $vencimento): Lancamento;

    /**
     * Lançamento desta assinatura, ou null se o id não pertencer a ela.
     */
    public function buscarDaAssinatura(int $assinaturaId, int $lancamentoId): ?Lancamento;

    /**
     * A assinatura tem ao menos um lançamento pago.
     */
    public function temPagos(int $assinaturaId): bool;

    /**
     * Marca o lançamento como pago no instante informado (UTC).
     */
    public function marcarPago(Lancamento $lancamento, CarbonInterface $pagoEm): void;

    /**
     * Cancela o lançamento.
     */
    public function cancelar(Lancamento $lancamento): void;

    /**
     * Corrige valor e vencimento de um lançamento em aberto.
     */
    public function atualizar(Lancamento $lancamento, string $valor, string $vencimento): void;

    /**
     * Cancela lançamentos em aberto com vencimento depois de $dataFim (Y-m-d).
     */
    public function cancelarAbertosComVencimentoApos(int $assinaturaId, string $dataFim): void;

    /**
     * Lançamento da competência (Y-m-d), ou null se ainda não existir.
     */
    public function buscarPorCompetencia(int $assinaturaId, string $competencia): ?Lancamento;

    /**
     * Cobranças em aberto já emitidas no banco e ainda sem e-mail.
     *
     * @return Collection<int, Lancamento>
     */
    public function pendentesDeEmail(): Collection;

    /**
     * Grava o identificador devolvido na emissão e limpa o erro anterior.
     */
    public function registrarSolicitacao(Lancamento $lancamento, string $codigoSolicitacao): void;

    /**
     * Grava a linha, o Pix e o instante em que o e-mail foi enviado.
     */
    public function registrarEnvio(
        Lancamento $lancamento,
        string $linhaDigitavel,
        ?string $pixCopiaCola,
        ?string $nossoNumero,
        CarbonInterface $enviadoEm,
    ): void;

    /**
     * Guarda o motivo pelo qual a emissão ou o e-mail não concluíram. Null limpa o erro.
     */
    public function registrarFalha(Lancamento $lancamento, ?string $erro): void;
}
