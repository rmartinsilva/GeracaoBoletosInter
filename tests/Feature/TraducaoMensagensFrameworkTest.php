<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\PermissoesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TraducaoMensagensFrameworkTest extends TestCase
{
    use RefreshDatabase;

    public function test_testes_rodam_com_locale_pt_br_e_fallback_en(): void
    {
        $this->assertSame('pt_BR', app()->getLocale());
        $this->assertSame('en', config('app.fallback_locale'));
    }

    public function test_regras_sem_mensagem_customizada_usam_a_traducao_e_o_nome_do_campo(): void
    {
        $this->seed(PermissoesSeeder::class);
        $usuario = Usuario::query()->where('login', 'admin')->firstOrFail();

        $response = $this->actingAs($usuario, 'painel')
            ->from('/painel/usuarios/criar')
            ->post('/painel/usuarios', [
                'name' => str_repeat('a', 256),
                'login' => str_repeat('b', 256),
                'password' => 'segredo123',
                'grupos' => 'administradores',
            ]);

        $response->assertRedirect('/painel/usuarios/criar');
        $response->assertSessionHasErrors([
            'name' => 'O campo nome não pode ter mais de 255 caracteres.',
            'login' => 'O campo login não pode ter mais de 255 caracteres.',
            'grupos' => 'O campo grupos deve ser uma lista de valores.',
        ]);
        $this->assertDatabaseMissing('usuarios', ['login' => str_repeat('b', 256)]);
    }

    public function test_resumo_dos_erros_em_json_fica_em_portugues(): void
    {
        $this->seed(PermissoesSeeder::class);
        $usuario = Usuario::query()->where('login', 'admin')->firstOrFail();

        $response = $this->actingAs($usuario, 'painel')->postJson('/painel/usuarios', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', 'O campo nome não pode ter mais de 255 caracteres. (e mais 2 erros)');
        $response->assertJsonPath('errors.login.0', 'O login é obrigatório.');
        $response->assertJsonPath('errors.password.0', 'A senha é obrigatória.');
    }

    public function test_mensagens_de_auth_paginacao_e_senhas_estao_em_portugues(): void
    {
        $this->assertSame('Essas credenciais não correspondem aos nossos registros.', __('auth.failed'));
        $this->assertSame('Muitas tentativas de login. Tente novamente em 30 segundos.', __('auth.throttle', ['seconds' => 30]));
        $this->assertSame('&laquo; Anterior', __('pagination.previous'));
        $this->assertSame('Próxima &raquo;', __('pagination.next'));
        $this->assertSame('Sua senha foi redefinida.', __('passwords.reset'));
        $this->assertSame('Este token de redefinição de senha é inválido.', __('passwords.token'));
    }

    public function test_pagina_de_erro_padrao_do_framework_fica_em_portugues(): void
    {
        $response = $this->get('/pagina-que-nao-existe');

        $response->assertNotFound();
        $response->assertSee('Página não encontrada');
        $response->assertDontSee('Not Found');
    }
}
