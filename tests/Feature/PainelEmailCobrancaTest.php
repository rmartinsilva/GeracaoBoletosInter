<?php

namespace Tests\Feature;

use App\Models\Acesso;
use App\Models\ConfiguracaoCobranca;
use App\Models\Grupo;
use App\Models\Usuario;
use Database\Seeders\PermissoesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PainelEmailCobrancaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissoesSeeder::class);
    }

    public function test_admin_ve_o_texto_padrao(): void
    {
        $this->comoAdmin()
            ->get(route('cobranca.email.edit'))
            ->assertOk()
            ->assertSee('E-mail da cobrança')
            ->assertSee('E-mail de falha')
            ->assertSee('{nome}', false)
            ->assertSee('{linha_digitavel}', false)
            ->assertSee('O boleto está em anexo.', false);
    }

    public function test_admin_atualiza_o_texto(): void
    {
        $texto = "Olá, {nome}.\n\nSeu boleto de {descricao} vence em {vencimento}.\n{linha_digitavel}\n{pix}";

        $this->comoAdmin()
            ->put(route('cobranca.email.update'), [
                'corpo_email' => $texto,
                'email_falha' => 'operador@exemplo.com',
            ])
            ->assertRedirect(route('cobranca.email.edit'))
            ->assertSessionHas('success', 'Configuração da cobrança atualizada com sucesso!');

        $configuracao = ConfiguracaoCobranca::atual();
        $this->assertSame($texto, $configuracao->corpo_email);
        $this->assertSame('operador@exemplo.com', $configuracao->email_falha);
    }

    public function test_sem_permissao_de_editar_cliente_nao_abre_o_texto(): void
    {
        $this->comoUsuarioCom(['cliente_visualizar'])
            ->get(route('cobranca.email.edit'))
            ->assertForbidden();
    }

    private function comoAdmin(): static
    {
        $admin = Usuario::query()->where('login', 'admin')->firstOrFail();

        return $this->actingAs($admin, 'painel')
            ->withSession(['permissoes' => $admin->acessos()]);
    }

    /**
     * @param  array<int, string>  $permissoes
     */
    private function comoUsuarioCom(array $permissoes): static
    {
        $usuario = Usuario::query()->firstOrCreate(
            ['login' => 'operador'],
            ['name' => 'Operador', 'password' => 'senha123'],
        );

        $grupo = Grupo::query()->create(['descricao' => 'Grupo '.uniqid()]);
        $grupo->acessos()->sync(Acesso::query()->whereIn('key', $permissoes)->pluck('id'));
        $usuario->grupos()->sync([$grupo->id]);

        return $this->actingAs($usuario, 'painel')
            ->withSession(['permissoes' => $usuario->fresh()->acessos()]);
    }
}
