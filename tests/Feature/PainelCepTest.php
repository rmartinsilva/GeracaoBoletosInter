<?php

namespace Tests\Feature;

use App\Models\Acesso;
use App\Models\Grupo;
use App\Models\Usuario;
use Database\Seeders\PermissoesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PainelCepTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissoesSeeder::class);
        Http::preventStrayRequests();
    }

    public function test_consulta_preenche_o_endereco_do_cep(): void
    {
        Http::fake([
            'https://viacep.com.br/ws/01310100/json/' => Http::response([
                'cep' => '01310-100',
                'logradouro' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
            ]),
        ]);

        $this->comoAdmin()
            ->getJson(route('cep.consultar', ['cep' => '01310-100']))
            ->assertOk()
            ->assertExactJson([
                'cep' => '01310100',
                'endereco' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
                'cidade' => 'São Paulo',
                'uf' => 'SP',
            ]);
    }

    public function test_cep_inexistente_avisa_sem_endereco(): void
    {
        Http::fake([
            'https://viacep.com.br/ws/00000000/json/' => Http::response(['erro' => true]),
        ]);

        $this->comoAdmin()
            ->getJson(route('cep.consultar', ['cep' => '00000000']))
            ->assertStatus(422)
            ->assertJson(['message' => 'CEP não encontrado.']);
    }

    public function test_falha_de_conexao_avisa_que_a_consulta_nao_saiu(): void
    {
        Http::fake(function (): void {
            throw new ConnectionException('tempo esgotado');
        });

        $this->comoAdmin()
            ->getJson(route('cep.consultar', ['cep' => '01310100']))
            ->assertStatus(422)
            ->assertJson(['message' => 'Não foi possível consultar o CEP agora.']);
    }

    public function test_sem_permissao_de_cliente_nao_consulta_o_cep(): void
    {
        $this->comoUsuarioCom(['usuario_visualizar'])
            ->getJson(route('cep.consultar', ['cep' => '01310100']))
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
