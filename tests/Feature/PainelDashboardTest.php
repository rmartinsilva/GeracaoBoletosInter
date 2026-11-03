<?php

namespace Tests\Feature;

use App\Models\Acesso;
use App\Models\Usuario;
use Database\Seeders\PermissoesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PainelDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_exibe_a_logo_da_usinaweb_no_topo(): void
    {
        $response = $this->get('/painel/login');

        $response->assertOk();
        $response->assertSee('assets/imagens/usinaWeb.png', false);
        $response->assertDontSee('logo-cliente-painel.png', false);
        $response->assertSee('Voltar');
        $response->assertSee(route('site.index'), false);
    }

    public function test_dashboard_mostra_apenas_clientes_e_usuarios(): void
    {
        $this->seed(PermissoesSeeder::class);

        $usuario = Usuario::query()->where('login', 'admin')->firstOrFail();

        $this->assertSame(
            [
                'cliente_cadastrar', 'cliente_deletar', 'cliente_editar', 'cliente_visualizar',
                'usuario_cadastrar', 'usuario_deletar', 'usuario_editar', 'usuario_visualizar',
            ],
            Acesso::query()->orderBy('key')->pluck('key')->all(),
        );

        $response = $this->actingAs($usuario, 'painel')->get('/painel');

        $response->assertOk();
        $response->assertSee('Geração de Cobranças');
        $response->assertSee('Clientes');
        $response->assertSee(route('clientes.index'), false);
        $response->assertSee('Usuários');
        $response->assertSee('Gerencie os clientes e os usuários do sistema');
        $response->assertDontSee('Banner Principal');
        $response->assertDontSee('Portfólio');
        $response->assertDontSee('Notícias');
        $response->assertDontSee('De Lollo');
        $response->assertDontSee('TechPro');
    }
}
