<?php

namespace Tests\Unit;

use App\Services\DataHoraService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use PHPUnit\Framework\TestCase;

class DataHoraServiceTest extends TestCase
{
    private DataHoraService $dataHora;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataHora = new DataHoraService('America/Sao_Paulo');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_agora_retorna_o_horario_de_brasilia(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-11-03 00:30:00', 'UTC'));

        $agora = $this->dataHora->agora();

        $this->assertSame('2026-11-02 21:30', $agora->format('Y-m-d H:i'));
        $this->assertSame('America/Sao_Paulo', $agora->getTimezone()->getName());
    }

    public function test_para_exibicao_converte_utc_para_brasilia_sem_mudar_o_instante(): void
    {
        $instante = CarbonImmutable::parse('2026-11-03 00:30:00', 'UTC');

        $convertido = $this->dataHora->paraExibicao($instante);

        $this->assertSame('2026-11-02 21:30:00', $convertido->format('Y-m-d H:i:s'));
        $this->assertTrue($convertido->equalTo($instante));
    }

    public function test_para_exibicao_nao_altera_o_carbon_mutavel_recebido(): void
    {
        $instante = Carbon::parse('2026-11-03 00:30:00', 'UTC');

        $this->dataHora->paraExibicao($instante);

        $this->assertSame('UTC', $instante->getTimezone()->getName());
        $this->assertSame('2026-11-03 00:30', $instante->format('Y-m-d H:i'));
    }

    public function test_para_utc_converte_horario_digitado_em_brasilia(): void
    {
        $utc = $this->dataHora->paraUtc('2026-11-02 21:30');

        $this->assertSame('2026-11-03 00:30:00', $utc->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $utc->getTimezone()->getName());
    }

    public function test_para_utc_aceita_o_formato_do_input_datetime_local(): void
    {
        $utc = $this->dataHora->paraUtc('2026-11-02T21:30');

        $this->assertSame('2026-11-03 00:30', $utc->format('Y-m-d H:i'));
    }

    public function test_para_utc_respeita_um_offset_explicito(): void
    {
        $utc = $this->dataHora->paraUtc('2026-11-02T21:30:00+00:00');

        $this->assertSame('2026-11-02 21:30', $utc->format('Y-m-d H:i'));
    }

    public function test_para_utc_rejeita_texto_que_nao_e_data(): void
    {
        $this->expectException(InvalidFormatException::class);

        $this->dataHora->paraUtc('não é uma data');
    }

    public function test_formatar_data_hora_converte_para_brasilia(): void
    {
        $instante = CarbonImmutable::parse('2026-11-03 00:30:00', 'UTC');

        $this->assertSame('02/11/2026 21:30', $this->dataHora->formatarDataHora($instante));
    }

    public function test_formatar_data_nao_converte_data_de_calendario(): void
    {
        $vencimento = CarbonImmutable::parse('2026-11-10', 'UTC');

        $this->assertSame('10/11/2026', $this->dataHora->formatarData($vencimento));
    }

    public function test_formatar_retorna_texto_vazio_para_valor_nulo(): void
    {
        $this->assertSame('', $this->dataHora->formatarData(null));
        $this->assertSame('', $this->dataHora->formatarDataHora(null));
    }
}
