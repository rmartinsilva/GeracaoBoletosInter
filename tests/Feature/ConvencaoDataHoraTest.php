<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Services\DataHoraService;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissoesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConvencaoDataHoraTest extends TestCase
{
    use RefreshDatabase;

    public function test_persistencia_fica_em_utc_na_aplicacao_e_no_mysql(): void
    {
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('+00:00', config('database.connections.mysql.timezone'));
        $this->assertSame('+00:00', config('database.connections.mariadb.timezone'));
    }

    public function test_container_injeta_o_fuso_de_exibicao_configurado(): void
    {
        config(['app.display_timezone' => 'America/Manaus']);

        $agora = app(DataHoraService::class)->agora();

        $this->assertSame('America/Manaus', $agora->getTimezone()->getName());
    }

    public function test_dashboard_mostra_a_data_de_brasilia_quando_em_utc_ja_e_o_dia_seguinte(): void
    {
        $this->seed(PermissoesSeeder::class);
        $usuario = Usuario::query()->where('login', 'admin')->firstOrFail();

        $this->travelTo(CarbonImmutable::parse('2030-05-05 00:30:00', 'UTC'));

        $response = $this->actingAs($usuario, 'painel')->get('/painel');

        $response->assertOk();
        $response->assertSee('04/05/2030');
        $response->assertDontSee('05/05/2030');
    }

    public function test_rodape_do_login_usa_o_ano_de_brasilia_na_virada_do_ano(): void
    {
        $this->travelTo(CarbonImmutable::parse('2031-01-01 01:00:00', 'UTC'));

        $response = $this->get('/painel/login');

        $response->assertOk();
        $response->assertSee('2008 - 2030');
        $response->assertDontSee('2008 - 2031');
    }
}
