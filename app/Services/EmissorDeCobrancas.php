<?php

namespace App\Services;

use App\DTO\Inter\EmitirCobrancaDTO;
use App\Enums\Periodicidade;
use App\Enums\SituacaoLancamento;
use App\Exceptions\RegraNegocioException;
use App\Mail\CobrancaEmitida;
use App\Models\Assinatura;
use App\Models\ConfiguracaoCobranca;
use App\Models\Lancamento;
use App\Repositories\Contracts\AssinaturaRepositoryInterface;
use App\Repositories\Contracts\LancamentoRepositoryInterface;
use App\Services\Inter\Contracts\CobrancaInterInterface;
use App\Services\Inter\ExcecaoInter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * No dia de geração emite o boleto da assinatura e, assim que o Inter devolve o PDF, envia o e-mail.
 * A emissão é assíncrona: uma cobrança pedida hoje pode só ter boleto na próxima execução.
 */
class EmissorDeCobrancas
{
    private const DIAS_AGENDA = 30;

    public function __construct(
        private AssinaturaRepositoryInterface $assinaturas,
        private LancamentoRepositoryInterface $lancamentos,
        private PlanejadorDeLancamentos $planejador,
        private DataHoraService $dataHora,
        private CobrancaInterInterface $cobranca,
        private CorpoEmailCobranca $corpoEmail,
    ) {}

    /**
     * @return array{emitidas: int, enviadas: int, aguardando: int, falhas: int}
     */
    public function executar(): array
    {
        $resumo = ['emitidas' => 0, 'enviadas' => 0, 'aguardando' => 0, 'falhas' => 0];
        $hoje = $this->dataHora->agora()->toDateString();
        $vistos = [];

        foreach ($this->assinaturas->listarAtivas() as $assinatura) {
            $lancamento = $this->lancamentoDoDia($assinatura, $hoje, $resumo);

            if ($lancamento === null) {
                continue;
            }

            $vistos[$lancamento->id] = true;
            $this->processar($lancamento, $resumo);
        }

        foreach ($this->lancamentos->pendentesDeEmail() as $lancamento) {
            if (isset($vistos[$lancamento->id])) {
                continue;
            }

            $this->processar($lancamento, $resumo);
        }

        return $resumo;
    }

    /**
     * @param  array{emitidas: int, enviadas: int, aguardando: int, falhas: int}  $resumo
     */
    private function lancamentoDoDia(Assinatura $assinatura, string $hoje, array &$resumo): ?Lancamento
    {
        if ($assinatura->periodicidade !== Periodicidade::Avulso && $assinatura->dia_geracao === null) {
            $resumo['falhas']++;

            return null;
        }

        $periodo = $this->planejador->periodoDoDia(
            $assinatura->periodicidade,
            $assinatura->data_inicio->toDateString(),
            $assinatura->data_fim?->toDateString(),
            $assinatura->dia_vencimento,
            $assinatura->dia_geracao,
            $hoje,
        );

        if ($periodo === null) {
            return null;
        }

        $existente = $this->lancamentos->buscarPorCompetencia($assinatura->id, $periodo['competencia']);

        if ($existente !== null) {
            return $existente;
        }

        return $this->lancamentos->criar(
            $assinatura->id,
            $periodo['competencia'],
            (string) $assinatura->valor,
            $periodo['vencimento'],
        );
    }

    /**
     * @param  array{emitidas: int, enviadas: int, aguardando: int, falhas: int}  $resumo
     */
    private function processar(Lancamento $lancamento, array &$resumo): void
    {
        $lancamento->loadMissing('assinatura.cliente');

        if ($lancamento->situacao !== SituacaoLancamento::Aberto || $lancamento->email_enviado_em !== null) {
            return;
        }

        try {
            if ($lancamento->codigo_solicitacao === null) {
                $this->emitir($lancamento);
                $resumo['emitidas']++;
            }

            $this->concluir($lancamento, $resumo);
        } catch (ExcecaoInter|RegraNegocioException $excecao) {
            $this->lancamentos->registrarFalha($lancamento, $excecao->getMessage());
            $resumo['falhas']++;
        } catch (Throwable $excecao) {
            report($excecao);
            $this->lancamentos->registrarFalha($lancamento, 'Não foi possível concluir a cobrança.');
            $resumo['falhas']++;
        }
    }

    private function emitir(Lancamento $lancamento): void
    {
        $assinatura = $lancamento->assinatura;
        $cliente = $assinatura->cliente;
        $pagador = $cliente->pagadorDaCobranca();

        $resposta = $this->cobranca->emitir(new EmitirCobrancaDTO(
            seuNumero: 'L'.str_pad((string) $lancamento->id, 14, '0', STR_PAD_LEFT),
            valorNominal: (string) $lancamento->valor,
            dataVencimento: $lancamento->vencimento->toDateString(),
            numDiasAgenda: self::DIAS_AGENDA,
            pagador: $pagador,
        ));

        $this->lancamentos->registrarSolicitacao($lancamento, $resposta['codigoSolicitacao']);
        $lancamento->refresh();
    }

    /**
     * @param  array{emitidas: int, enviadas: int, aguardando: int, falhas: int}  $resumo
     */
    private function concluir(Lancamento $lancamento, array &$resumo): void
    {
        $consulta = $this->cobranca->consultar((string) $lancamento->codigo_solicitacao);
        $situacao = $consulta['cobranca']['situacao'] ?? null;

        if ($situacao === 'FALHA_EMISSAO') {
            $this->lancamentos->registrarFalha($lancamento, 'O Banco Inter recusou a emissão desta cobrança.');
            $resumo['falhas']++;

            return;
        }

        $linha = $consulta['boleto']['linhaDigitavel'] ?? null;
        $pix = $consulta['pix']['pixCopiaECola'] ?? null;
        $nossoNumero = $consulta['boleto']['nossoNumero'] ?? null;

        if (! is_string($linha) || $linha === '') {
            $this->lancamentos->registrarFalha($lancamento, null);
            $resumo['aguardando']++;

            return;
        }

        try {
            $pdf = $this->cobranca->recuperarPdf((string) $lancamento->codigo_solicitacao);
        } catch (ExcecaoInter) {
            $this->lancamentos->registrarFalha($lancamento, null);
            $resumo['aguardando']++;

            return;
        }

        $caminho = 'boletos/'.$lancamento->id.'.pdf';
        Storage::disk('local')->put($caminho, $pdf);

        $lancamento->linha_digitavel = $linha;
        $lancamento->pix_copia_cola = is_string($pix) ? $pix : null;

        $assinatura = $lancamento->assinatura;
        $cliente = $assinatura->cliente;
        $texto = $this->corpoEmail->montar(
            ConfiguracaoCobranca::atual()->corpo_email,
            $cliente,
            $assinatura,
            $lancamento,
        );

        Mail::to($cliente->email)->send(new CobrancaEmitida(
            texto: $texto,
            assunto: 'Cobrança: '.$assinatura->descricao,
            caminhoPdf: $caminho,
        ));

        $this->lancamentos->registrarEnvio(
            $lancamento,
            $linha,
            is_string($pix) ? $pix : null,
            is_scalar($nossoNumero) ? (string) $nossoNumero : null,
            now(),
        );
        $resumo['enviadas']++;
    }
}
