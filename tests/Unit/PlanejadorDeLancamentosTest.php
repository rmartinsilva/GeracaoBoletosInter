<?php

namespace Tests\Unit;

use App\Enums\Periodicidade;
use App\Exceptions\RegraNegocioException;
use App\Services\PlanejadorDeLancamentos;
use PHPUnit\Framework\TestCase;

class PlanejadorDeLancamentosTest extends TestCase
{
    private PlanejadorDeLancamentos $planejador;

    protected function setUp(): void
    {
        parent::setUp();

        $this->planejador = new PlanejadorDeLancamentos;
    }

    public function test_mensal_comeca_no_proximo_mes_quando_o_dia_ja_passou(): void
    {
        $planejado = $this->planejador->planejar(
            Periodicidade::Mensal,
            '2031-04-20',
            null,
            10,
            '2031-06-15',
        );

        $this->assertFalse($planejado['limitado']);
        $this->assertSame([
            ['competencia' => '2031-05-01', 'vencimento' => '2031-05-10'],
            ['competencia' => '2031-06-01', 'vencimento' => '2031-06-10'],
        ], $planejado['periodos']);
    }

    public function test_dia_31_cai_no_ultimo_dia_de_fevereiro(): void
    {
        $planejado = $this->planejador->planejar(
            Periodicidade::Mensal,
            '2031-01-15',
            null,
            31,
            '2031-03-20',
        );

        $this->assertSame([
            ['competencia' => '2031-01-01', 'vencimento' => '2031-01-31'],
            ['competencia' => '2031-02-01', 'vencimento' => '2031-02-28'],
            ['competencia' => '2031-03-01', 'vencimento' => '2031-03-31'],
        ], $planejado['periodos']);
    }

    public function test_inicio_futuro_gera_somente_o_primeiro_vencimento(): void
    {
        $planejado = $this->planejador->planejar(
            Periodicidade::Mensal,
            '2031-08-01',
            null,
            10,
            '2031-06-15',
        );

        $this->assertSame([
            ['competencia' => '2031-08-01', 'vencimento' => '2031-08-10'],
        ], $planejado['periodos']);
    }

    public function test_anual_gera_um_vencimento_por_ano_ate_o_ano_atual(): void
    {
        $planejado = $this->planejador->planejar(
            Periodicidade::Anual,
            '2029-06-10',
            null,
            10,
            '2031-06-15',
        );

        $this->assertSame([
            ['competencia' => '2029-06-01', 'vencimento' => '2029-06-10'],
            ['competencia' => '2030-06-01', 'vencimento' => '2030-06-10'],
            ['competencia' => '2031-06-01', 'vencimento' => '2031-06-10'],
        ], $planejado['periodos']);
    }

    public function test_nao_gera_vencimento_depois_do_encerramento(): void
    {
        $planejado = $this->planejador->planejar(
            Periodicidade::Mensal,
            '2031-04-20',
            '2031-05-31',
            10,
            '2031-06-15',
        );

        $this->assertSame([
            ['competencia' => '2031-05-01', 'vencimento' => '2031-05-10'],
        ], $planejado['periodos']);
    }

    public function test_limita_aos_120_lancamentos_mais_recentes(): void
    {
        $planejado = $this->planejador->planejar(
            Periodicidade::Mensal,
            '2010-01-10',
            null,
            10,
            '2031-06-15',
        );

        $this->assertTrue($planejado['limitado']);
        $this->assertCount(120, $planejado['periodos']);
        $this->assertSame('2021-07-01', $planejado['periodos'][0]['competencia']);
        $this->assertSame('2031-06-01', $planejado['periodos'][119]['competencia']);
        $this->assertSame('2031-06-10', $planejado['periodos'][119]['vencimento']);
    }

    public function test_proximo_avanca_um_mes(): void
    {
        $proximo = $this->planejador->proximo(
            Periodicidade::Mensal,
            '2031-06-01',
            '2031-04-10',
            null,
            10,
        );

        $this->assertSame([
            'competencia' => '2031-07-01',
            'vencimento' => '2031-07-10',
        ], $proximo);
    }

    public function test_proximo_depois_do_encerramento_falha(): void
    {
        $this->expectException(RegraNegocioException::class);
        $this->expectExceptionMessage('Não há próximo lançamento: a data fica depois do encerramento da assinatura.');

        $this->planejador->proximo(Periodicidade::Mensal, '2031-06-01', '2031-04-10', '2031-06-30', 10);
    }

    public function test_gera_no_dia_5_a_cobranca_que_vence_no_dia_10(): void
    {
        $periodo = $this->planejador->periodoDoDia(
            Periodicidade::Mensal,
            '2031-04-10',
            null,
            10,
            5,
            '2031-06-05',
        );

        $this->assertSame([
            'competencia' => '2031-06-01',
            'vencimento' => '2031-06-10',
        ], $periodo);
    }

    public function test_dia_de_geracao_depois_do_vencimento_emite_no_mes_anterior(): void
    {
        $periodo = $this->planejador->periodoDoDia(
            Periodicidade::Mensal,
            '2031-01-10',
            null,
            10,
            25,
            '2031-05-25',
        );

        $this->assertSame([
            'competencia' => '2031-06-01',
            'vencimento' => '2031-06-10',
        ], $periodo);
    }

    public function test_dia_31_de_geracao_cai_no_ultimo_dia_de_fevereiro(): void
    {
        $periodo = $this->planejador->periodoDoDia(
            Periodicidade::Mensal,
            '2031-01-10',
            null,
            10,
            31,
            '2031-02-28',
        );

        $this->assertSame([
            'competencia' => '2031-03-01',
            'vencimento' => '2031-03-10',
        ], $periodo);
    }

    public function test_fora_do_dia_de_geracao_nao_devolve_periodo(): void
    {
        $this->assertNull($this->planejador->periodoDoDia(
            Periodicidade::Mensal,
            '2031-01-10',
            null,
            10,
            5,
            '2031-06-06',
        ));
    }

    public function test_anual_so_gera_no_mes_de_aniversario(): void
    {
        $this->assertNull($this->planejador->periodoDoDia(
            Periodicidade::Anual,
            '2030-06-10',
            null,
            10,
            5,
            '2031-07-05',
        ));

        $this->assertSame([
            'competencia' => '2031-06-01',
            'vencimento' => '2031-06-10',
        ], $this->planejador->periodoDoDia(
            Periodicidade::Anual,
            '2030-06-10',
            null,
            10,
            5,
            '2031-06-05',
        ));
    }
}
