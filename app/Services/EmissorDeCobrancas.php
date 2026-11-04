<?php

namespace App\Services;

use App\DTO\Inter\EmitirCobrancaDTO;
use App\Enums\SituacaoLancamento;
use App\Exceptions\RegraNegocioException;
use App\Mail\CobrancaEmitida;
use App\Mail\FalhaCobranca;
use App\Models\Assinatura;
use App\Models\Cliente;
use App\Models\ConfiguracaoCobranca;
use App\Models\FilaEmissao;
use App\Models\Lancamento;
use App\Repositories\Contracts\AssinaturaRepositoryInterface;
use App\Repositories\Contracts\LancamentoRepositoryInterface;
use App\Services\Inter\Contracts\CobrancaInterInterface;
use App\Services\Inter\ExcecaoInter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * No dia de geração emite o boleto da assinatura e, assim que o Inter devolve o PDF, envia o e-mail.
 * A emissão é assíncrona: uma cobrança pedida hoje pode só ter boleto na próxima execução.
 * Cobrança de assinatura que já falhou é tentada de novo em qualquer dia.
 * O lote não chama o banco de uma vez: cada cobrança entra na fila e sai uma por minuto.
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
     * @return array{enfileiradas: int, falhas: int}
     */
    public function executar(): array
    {
        $resumo = ['enfileiradas' => 0, 'falhas' => 0];
        $hoje = $this->dataHora->agora()->toDateString();
        $vistos = [];
        $quando = $this->proximoHorarioDaFila();

        foreach ($this->assinaturas->listarAtivas() as $assinatura) {
            $lancamento = $this->lancamentoDoDia($assinatura, $hoje, $resumo);

            if ($lancamento === null || isset($vistos[$lancamento->id])) {
                continue;
            }

            $vistos[$lancamento->id] = true;
            $quando = $this->enfileirar($lancamento, $quando, $resumo);
        }

        foreach ([$this->lancamentos->pendentesDeEmail(), $this->lancamentos->avulsosEmAberto(), $this->lancamentos->assinaturasComFalhaDeEmissao()] as $pendencias) {
            foreach ($pendencias as $lancamento) {
                if (isset($vistos[$lancamento->id])) {
                    continue;
                }

                $vistos[$lancamento->id] = true;
                $quando = $this->enfileirar($lancamento, $quando, $resumo);
            }
        }

        return $resumo;
    }

    /**
     * Envia a próxima cobrança cujo horário já chegou. Null quando a fila não tem nada pronto.
     *
     * @return array{emitidas: int, enviadas: int, aguardando: int, falhas: int}|null
     */
    public function processarProxima(): ?array
    {
        FilaEmissao::query()
            ->whereNull('processado_em')
            ->whereNotNull('reservado_em')
            ->where('reservado_em', '<=', now()->subMinutes(2))
            ->update(['reservado_em' => null]);

        $this->reabrirAguardandoBoleto();

        $item = $this->reservarProxima();

        if ($item === null) {
            return null;
        }

        $resumo = ['emitidas' => 0, 'enviadas' => 0, 'aguardando' => 0, 'falhas' => 0];
        $lancamento = $item->lancamento;

        if ($lancamento !== null) {
            $this->processar($lancamento, $resumo);
        }

        if ($resumo['aguardando'] > 0) {
            if ($lancamento !== null && $item->created_at !== null && $item->created_at->lte(now()->subHours(6))) {
                $this->notificarFalha($lancamento, 'O boleto continuou em processamento no Banco Inter.');
                $item->update([
                    'reservado_em' => null,
                    'processado_em' => now(),
                ]);

                return $resumo;
            }

            $item->update([
                'reservado_em' => null,
                'disponivel_em' => now()->addMinute(),
            ]);

            return $resumo;
        }

        $item->update([
            'reservado_em' => null,
            'processado_em' => now(),
        ]);

        return $resumo;
    }

    /**
     * @param  array{enfileiradas: int, falhas: int}  $resumo
     */
    private function enfileirar(Lancamento $lancamento, Carbon $quando, array &$resumo): Carbon
    {
        if ($lancamento->situacao !== SituacaoLancamento::Aberto || $lancamento->email_enviado_em !== null) {
            return $quando;
        }

        $existente = FilaEmissao::query()->where('lancamento_id', $lancamento->id)->first();

        if ($existente !== null && $existente->processado_em === null) {
            return $quando;
        }

        FilaEmissao::query()->updateOrCreate(
            ['lancamento_id' => $lancamento->id],
            [
                'disponivel_em' => $quando,
                'reservado_em' => null,
                'processado_em' => null,
            ],
        );

        $resumo['enfileiradas']++;

        return $quando->copy()->addMinute();
    }

    private function proximoHorarioDaFila(): Carbon
    {
        $ultimo = FilaEmissao::query()
            ->whereNull('processado_em')
            ->max('disponivel_em');

        if (! is_string($ultimo) && ! $ultimo instanceof Carbon) {
            return now();
        }

        $horario = Carbon::parse($ultimo)->addMinute();

        return $horario->greaterThan(now()) ? $horario : now();
    }

    /**
     * Cobrança já aceita pelo banco e ainda sem e-mail volta para a fila,
     * mesmo que a tentativa anterior tenha sido encerrada.
     */
    private function reabrirAguardandoBoleto(): void
    {
        foreach ($this->lancamentos->pendentesDeEmail() as $lancamento) {
            if ($lancamento->erro_emissao !== null) {
                continue;
            }

            $fila = FilaEmissao::query()->where('lancamento_id', $lancamento->id)->first();

            if ($fila !== null && $fila->processado_em === null) {
                continue;
            }

            FilaEmissao::query()->updateOrCreate(
                ['lancamento_id' => $lancamento->id],
                [
                    'disponivel_em' => now(),
                    'reservado_em' => null,
                    'processado_em' => null,
                ],
            );
        }
    }

    private function reservarProxima(): ?FilaEmissao
    {
        return DB::transaction(function (): ?FilaEmissao {
            $item = FilaEmissao::query()
                ->whereNull('fila_emissoes.processado_em')
                ->whereNull('fila_emissoes.reservado_em')
                ->where('fila_emissoes.disponivel_em', '<=', now())
                ->orderByRaw('case when exists (
                    select 1 from lancamentos
                    where lancamentos.id = fila_emissoes.lancamento_id
                      and lancamentos.codigo_solicitacao is not null
                      and lancamentos.email_enviado_em is null
                ) then 0 else 1 end')
                ->orderBy('fila_emissoes.disponivel_em')
                ->orderBy('fila_emissoes.id')
                ->lockForUpdate()
                ->first();

            if ($item === null) {
                return null;
            }

            $item->update(['reservado_em' => now()]);

            return $item->load('lancamento');
        });
    }

    /**
     * Emite uma cobrança avulsa na hora. O cron das 08:00 retoma o que ficar sem e-mail.
     *
     * @return array{emitidas: int, enviadas: int, aguardando: int, falhas: int}
     */
    public function emitirLancamento(Lancamento $lancamento): array
    {
        $resumo = ['emitidas' => 0, 'enviadas' => 0, 'aguardando' => 0, 'falhas' => 0];
        $this->processar($lancamento, $resumo);

        if ($resumo['aguardando'] > 0) {
            $ignorado = ['enfileiradas' => 0, 'falhas' => 0];
            $this->enfileirar($lancamento->refresh(), now()->addMinute(), $ignorado);
        }

        return $resumo;
    }

    /**
     * @param  array{enfileiradas: int, falhas: int}  $resumo
     */
    private function lancamentoDoDia(Assinatura $assinatura, string $hoje, array &$resumo): ?Lancamento
    {
        if ($assinatura->dia_geracao === null) {
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
        $lancamento->loadMissing('assinatura.cliente', 'cliente');

        if ($lancamento->situacao !== SituacaoLancamento::Aberto || $lancamento->email_enviado_em !== null) {
            return;
        }

        try {
            if ($lancamento->codigo_solicitacao === null) {
                $this->emitir($lancamento);
                $resumo['emitidas']++;
            }

            $this->concluir($lancamento, $resumo);
        } catch (ExcecaoInter $excecao) {
            if ($lancamento->codigo_solicitacao !== null) {
                $this->lancamentos->registrarFalha($lancamento, null);
                $resumo['aguardando']++;

                return;
            }

            $this->notificarFalha($lancamento, $excecao->getMessage());
            $resumo['falhas']++;
        } catch (RegraNegocioException $excecao) {
            $this->notificarFalha($lancamento, $excecao->getMessage());
            $resumo['falhas']++;
        } catch (Throwable $excecao) {
            report($excecao);
            $this->notificarFalha($lancamento, 'Não foi possível concluir a cobrança.');
            $resumo['falhas']++;
        }
    }

    private function emitir(Lancamento $lancamento): void
    {
        [$cliente] = $this->origem($lancamento);
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
            $this->notificarFalha($lancamento, 'O Banco Inter recusou a emissão desta cobrança.');
            $resumo['falhas']++;

            return;
        }

        if ($situacao === 'EM_PROCESSAMENTO') {
            $this->lancamentos->registrarFalha($lancamento, null);
            $resumo['aguardando']++;

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

        [$cliente, $descricao] = $this->origem($lancamento);
        $configuracao = ConfiguracaoCobranca::atual();
        $texto = $this->corpoEmail->montar(
            $configuracao->corpo_email,
            $cliente,
            $descricao,
            $lancamento,
        );

        $destinos = $cliente->emailsDeCobranca();

        if ($destinos === []) {
            throw new RegraNegocioException('O cliente '.$cliente->nome.' precisa de um e-mail para receber o boleto.');
        }

        $envio = Mail::to($destinos);
        $copia = is_string($configuracao->email_copia) ? mb_strtolower(trim($configuracao->email_copia)) : '';

        if ($copia !== '' && ! in_array($copia, $destinos, true)) {
            $envio->bcc($copia);
        }

        $envio->send(new CobrancaEmitida(
            texto: $texto,
            assunto: 'Cobrança: '.$descricao,
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

    /**
     * Grava a falha e avisa o responsável uma vez por motivo. O mesmo erro não gera outro e-mail.
     */
    private function notificarFalha(Lancamento $lancamento, string $mensagem): void
    {
        $jaAvisada = $lancamento->erro_emissao === $mensagem && $lancamento->falha_notificada_em !== null;

        $this->lancamentos->registrarFalha($lancamento, $mensagem);

        if ($jaAvisada) {
            return;
        }

        $destino = ConfiguracaoCobranca::atual()->email_falha;

        if (! is_string($destino) || $destino === '') {
            return;
        }

        [$cliente, $descricao] = $this->origem($lancamento);

        try {
            Mail::to($destino)->send(new FalhaCobranca(
                texto: implode("\n\n", [
                    'A cobrança não foi emitida.',
                    'Cliente: '.$cliente->nome,
                    'Cobrança: '.$descricao,
                    'Valor: R$ '.number_format((float) $lancamento->valor, 2, ',', '.'),
                    'Vencimento: '.$lancamento->vencimento->format('d/m/Y'),
                    'Motivo: '.$mensagem,
                ]),
                assunto: 'Falha na cobrança: '.$descricao,
            ));

            $this->lancamentos->registrarAvisoDeFalha($lancamento, now());
        } catch (Throwable $excecao) {
            report($excecao);
        }
    }

    /**
     * @return array{0: Cliente, 1: string}
     */
    private function origem(Lancamento $lancamento): array
    {
        $lancamento->loadMissing('assinatura.cliente', 'cliente');
        $assinatura = $lancamento->assinatura;

        if ($assinatura !== null) {
            return [$assinatura->cliente, $assinatura->descricao];
        }

        if ($lancamento->cliente === null) {
            throw new RegraNegocioException('A cobrança avulsa não tem cliente.');
        }

        return [$lancamento->cliente, (string) $lancamento->descricao];
    }
}
