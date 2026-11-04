<?php

namespace Tests\Feature;

use App\Enums\SituacaoLancamento;
use App\Models\FilaEmissao;
use App\Models\Lancamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookCobrancaTest extends TestCase
{
    use RefreshDatabase;

    private const CODIGO = '497f6eca-6276-4993-bfeb-53cbbbba6f08';

    public function test_recebido_marca_o_lancamento_como_pago(): void
    {
        $lancamento = Lancamento::factory()->create([
            'codigo_solicitacao' => self::CODIGO,
            'valor' => 150,
        ]);

        $this->postJson(route('inter.cobrancas.webhook'), [[
            'codigoSolicitacao' => self::CODIGO,
            'seuNumero' => $this->seuNumero($lancamento),
            'situacao' => 'RECEBIDO',
            'dataHoraSituacao' => '2026-11-04T14:30:00Z',
            'valorTotalRecebido' => '150.00',
            'origemRecebimento' => 'PIX',
        ]])->assertOk();

        $lancamento->refresh();
        $this->assertSame(SituacaoLancamento::Pago, $lancamento->situacao);
        $this->assertSame('2026-11-04 14:30:00', $lancamento->pago_em?->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('150.00', $lancamento->valor);
    }

    public function test_o_mesmo_aviso_nao_troca_a_data_do_pagamento(): void
    {
        $lancamento = Lancamento::factory()->create(['codigo_solicitacao' => self::CODIGO]);

        $this->avisar(self::CODIGO, 'RECEBIDO', '2026-11-04T14:30:00Z');
        $this->avisar(self::CODIGO, 'RECEBIDO', '2026-11-05T10:00:00Z');

        $this->assertSame(
            '2026-11-04 14:30:00',
            $lancamento->fresh()->pago_em?->utc()->format('Y-m-d H:i:s'),
        );
    }

    public function test_marcado_recebido_tambem_quita(): void
    {
        $lancamento = Lancamento::factory()->create(['codigo_solicitacao' => self::CODIGO]);

        $this->avisar(self::CODIGO, 'MARCADO_RECEBIDO', '2026-11-04T11:00:00-03:00');

        $this->assertSame(SituacaoLancamento::Pago, $lancamento->fresh()->situacao);
        $this->assertSame('2026-11-04 14:00:00', $lancamento->fresh()->pago_em?->utc()->format('Y-m-d H:i:s'));
    }

    public function test_cancelado_encerra_lancamento_em_aberto(): void
    {
        $lancamento = Lancamento::factory()->create(['codigo_solicitacao' => self::CODIGO]);

        $this->avisar(self::CODIGO, 'CANCELADO', '2026-11-04T14:30:00Z');

        $atual = $lancamento->fresh();
        $this->assertSame(SituacaoLancamento::Cancelado, $atual->situacao);
        $this->assertNull($atual->pago_em);
    }

    public function test_cancelado_nao_desfaz_pagamento(): void
    {
        $lancamento = Lancamento::factory()->pago()->create(['codigo_solicitacao' => self::CODIGO]);

        $this->avisar(self::CODIGO, 'CANCELADO', '2026-11-04T14:30:00Z');

        $this->assertSame(SituacaoLancamento::Pago, $lancamento->fresh()->situacao);
    }

    public function test_expirado_deixa_o_lancamento_em_aberto(): void
    {
        $lancamento = Lancamento::factory()->create(['codigo_solicitacao' => self::CODIGO]);

        $this->avisar(self::CODIGO, 'EXPIRADO', '2026-11-04T14:30:00Z');

        $this->assertSame(SituacaoLancamento::Aberto, $lancamento->fresh()->situacao);
    }

    public function test_a_receber_coloca_o_boleto_na_frente_da_fila(): void
    {
        $lancamento = Lancamento::factory()->create([
            'codigo_solicitacao' => self::CODIGO,
        ]);
        FilaEmissao::query()->create([
            'lancamento_id' => $lancamento->id,
            'disponivel_em' => '2031-06-05 18:00:00',
            'processado_em' => '2031-06-05 15:00:00',
        ]);

        $this->avisar(self::CODIGO, 'A_RECEBER', '2026-11-04T14:30:00Z');

        $fila = FilaEmissao::query()->where('lancamento_id', $lancamento->id)->sole();
        $this->assertNull($fila->processado_em);
        $this->assertTrue($fila->prioridade);
        $this->assertTrue($fila->disponivel_em->lessThanOrEqualTo(now()));
        $this->assertSame(SituacaoLancamento::Aberto, $lancamento->fresh()->situacao);
    }

    public function test_cobranca_desconhecida_nao_altera_nada(): void
    {
        Lancamento::factory()->create(['codigo_solicitacao' => self::CODIGO]);

        $this->avisar('11111111-1111-1111-1111-111111111111', 'RECEBIDO', '2026-11-04T14:30:00Z');

        $this->assertSame(SituacaoLancamento::Aberto, Lancamento::query()->sole()->situacao);
    }

    public function test_seu_numero_encontra_a_cobranca_ainda_sem_codigo(): void
    {
        $lancamento = Lancamento::factory()->create(['codigo_solicitacao' => null]);

        $this->postJson(route('inter.cobrancas.webhook'), [[
            'codigoSolicitacao' => self::CODIGO,
            'seuNumero' => $this->seuNumero($lancamento),
            'situacao' => 'RECEBIDO',
            'dataHoraSituacao' => '2026-11-04T14:30:00Z',
        ]])->assertOk();

        $atual = $lancamento->fresh();
        $this->assertSame(SituacaoLancamento::Pago, $atual->situacao);
        $this->assertSame(self::CODIGO, $atual->codigo_solicitacao);
    }

    public function test_aviso_invalido_nao_e_aceito(): void
    {
        $this->call('POST', route('inter.cobrancas.webhook'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], 'não é json')->assertStatus(400);
    }

    private function avisar(string $codigo, string $situacao, string $quando): void
    {
        $this->postJson(route('inter.cobrancas.webhook'), [[
            'codigoSolicitacao' => $codigo,
            'situacao' => $situacao,
            'dataHoraSituacao' => $quando,
        ]])->assertOk();
    }

    private function seuNumero(Lancamento $lancamento): string
    {
        return 'L'.str_pad((string) $lancamento->id, 14, '0', STR_PAD_LEFT);
    }
}
