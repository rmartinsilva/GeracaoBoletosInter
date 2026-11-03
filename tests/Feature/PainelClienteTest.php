<?php

namespace Tests\Feature;

use App\Enums\SituacaoAssinatura;
use App\Enums\SituacaoCliente;
use App\Enums\SituacaoLancamento;
use App\Models\Acesso;
use App\Models\Assinatura;
use App\Models\Cliente;
use App\Models\Grupo;
use App\Models\Lancamento;
use App\Models\Usuario;
use Database\Seeders\PermissoesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PainelClienteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissoesSeeder::class);
    }

    public function test_visitante_e_redirecionado_para_o_login(): void
    {
        $this->get('/painel/clientes')->assertRedirect(route('login'));
    }

    public function test_visitante_na_ficha_e_redirecionado_para_o_login(): void
    {
        $cliente = Cliente::factory()->create();

        $this->get('/painel/clientes/'.$cliente->id)->assertRedirect(route('login'));
    }

    public function test_listagem_exibe_o_cliente_e_a_quantidade_de_assinaturas_ativas(): void
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'Padaria Pão Quente',
            'email' => 'financeiro@paoquente.com.br',
            'nome_contato' => 'Joana Lima',
        ]);
        Assinatura::factory()->mensal()->create([
            'cliente_id' => $cliente->id,
            'valor' => 1234.5,
        ]);
        Assinatura::factory()->encerrada()->create(['cliente_id' => $cliente->id]);

        $response = $this->comoAdmin()->get('/painel/clientes');

        $response->assertOk();
        $response->assertSee('Padaria Pão Quente');
        $response->assertSee('financeiro@paoquente.com.br');
        $response->assertSee('Joana Lima');
        $response->assertSee('1 ativa');
        $response->assertSee('Assinaturas');
        $response->assertDontSee('2 ativas');
        $response->assertDontSee('R$ 1.234,50');
        $response->assertSee('Novo Cliente');
        $response->assertSee('Inativar');
        $response->assertSee(route('clientes.inativar', $cliente->id), false);
        $response->assertSee(route('clientes.create'), false);
        $response->assertSee(route('clientes.show', $cliente->id), false);
    }

    public function test_listagem_vazia_mostra_aviso(): void
    {
        $this->comoAdmin()
            ->get('/painel/clientes')
            ->assertOk()
            ->assertSee('Nenhum cliente encontrado.');
    }

    #[DataProvider('buscas')]
    public function test_busca_filtra_por_nome_email_ou_contato(string $termo, string $encontrado): void
    {
        Cliente::factory()->create(['nome' => 'Oficina Central', 'email' => 'oficina@exemplo.com', 'nome_contato' => 'Carlos']);
        Cliente::factory()->create(['nome' => 'Mercado Bom Preço', 'email' => 'mercado@exemplo.com', 'nome_contato' => 'Beatriz']);
        Cliente::factory()->create(['nome' => 'Escola Saber', 'email' => 'contato@saber.edu.br', 'nome_contato' => 'Renata']);

        $response = $this->comoAdmin()->get('/painel/clientes?search='.urlencode($termo));

        $response->assertOk();
        $response->assertSee($encontrado);
        foreach (array_diff(['Oficina Central', 'Mercado Bom Preço', 'Escola Saber'], [$encontrado]) as $outro) {
            $response->assertDontSee($outro);
        }
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function buscas(): array
    {
        return [
            'por nome' => ['Oficina', 'Oficina Central'],
            'por e-mail' => ['saber.edu', 'Escola Saber'],
            'por contato' => ['Beatriz', 'Mercado Bom Preço'],
        ];
    }

    public function test_primeira_pagina_mostra_quinze_clientes_e_link_para_a_proxima(): void
    {
        $this->criarClientesNumerados(16);

        $this->comoAdmin()->get('/painel/clientes?search=Cliente')
            ->assertSee('Cliente 15')
            ->assertDontSee('Cliente 16')
            ->assertSee('Página 1 de 2')
            ->assertSee('search=Cliente&amp;page=2', false);
    }

    public function test_segunda_pagina_mostra_o_restante_mantendo_a_busca(): void
    {
        $this->criarClientesNumerados(16);

        $this->comoAdmin()->get('/painel/clientes?search=Cliente&page=2')
            ->assertSee('Cliente 16')
            ->assertDontSee('Cliente 15')
            ->assertSee('Página 2 de 2')
            ->assertSee('search=Cliente&amp;page=1', false);
    }

    public function test_formulario_de_cadastro_nao_pede_valor_nem_periodicidade(): void
    {
        $response = $this->comoAdmin()->get('/painel/clientes/criar');

        $response->assertOk()
            ->assertSee('Novo Cliente')
            ->assertSee('O valor e a periodicidade ficam na assinatura.')
            ->assertSee('Informe o CEP para preencher o endereço.')
            ->assertSee('data-mascara="cpf-cnpj"', false)
            ->assertSee('data-mascara="numero"', false)
            ->assertSee('data-mascara="cep"', false)
            ->assertSee('pattern="[^@\s]+@[^@\s]+\.[^@\s]+"', false)
            ->assertDontSee('name="valor"', false)
            ->assertDontSee('name="periodicidade"', false);

        $html = $response->getContent();
        $this->assertLessThan(strpos($html, 'id="endereco"'), strpos($html, 'id="cep"'));
    }

    public function test_cadastra_cliente(): void
    {
        $response = $this->comoAdmin()->post('/painel/clientes', [
            'nome' => 'Padaria Pão Quente',
            'email' => 'financeiro@paoquente.com.br',
            'nome_contato' => 'Joana Lima',
        ]);

        $cliente = Cliente::query()->sole();

        $response->assertRedirect(route('clientes.show', $cliente->id));
        $response->assertSessionHas('success', 'Cliente cadastrado com sucesso!');
        $this->assertSame('Padaria Pão Quente', $cliente->nome);
        $this->assertSame('financeiro@paoquente.com.br', $cliente->email);
        $this->assertSame('Joana Lima', $cliente->nome_contato);
        $this->assertSame(SituacaoCliente::Ativo, $cliente->situacao);
    }

    public function test_cadastro_exige_todos_os_campos(): void
    {
        $this->comoAdmin()
            ->post('/painel/clientes', [])
            ->assertSessionHasErrors([
                'nome' => 'O nome do cliente é obrigatório.',
                'email' => 'O e-mail é obrigatório.',
                'nome_contato' => 'O nome do contato é obrigatório.',
            ]);

        $this->assertSame(0, Cliente::query()->count());
    }

    #[DataProvider('dadosInvalidos')]
    public function test_cadastro_rejeita_dados_invalidos(string $campo, string $valor, string $mensagem): void
    {
        $this->comoAdmin()
            ->post('/painel/clientes', $this->dadosValidos([$campo => $valor]))
            ->assertSessionHasErrors([$campo => $mensagem]);

        $this->assertSame(0, Cliente::query()->count());
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function dadosInvalidos(): array
    {
        return [
            'e-mail inválido' => ['email', 'financeiro-sem-arroba', 'Informe um e-mail válido.'],
            'e-mail sem domínio' => ['email', 'financeiro@cliente', 'Informe um e-mail válido.'],
            'nome vazio' => ['nome', '', 'O nome do cliente é obrigatório.'],
        ];
    }

    public function test_cadastro_guarda_documento_numero_e_email_normalizados(): void
    {
        $this->comoAdmin()->post('/painel/clientes', $this->dadosValidos([
            'email' => 'Financeiro@PaoQuente.com.br',
            'tipo_pessoa' => 'juridica',
            'cpf_cnpj' => '12.345.678/0001-99',
            'numero' => 's/n',
            'uf' => 'go',
        ]))->assertSessionHasNoErrors();

        $cliente = Cliente::query()->sole();
        $this->assertSame('financeiro@paoquente.com.br', $cliente->email);
        $this->assertSame('12345678000199', $cliente->cpf_cnpj);
        $this->assertSame('S/N', $cliente->numero);
        $this->assertSame('GO', $cliente->uf);
    }

    public function test_pessoa_fisica_nao_aceita_cnpj(): void
    {
        $this->comoAdmin()->post('/painel/clientes', $this->dadosValidos([
            'tipo_pessoa' => 'fisica',
            'cpf_cnpj' => '12.345.678/0001-99',
        ]))->assertSessionHasErrors(['cpf_cnpj' => 'Pessoa física usa CPF com 11 dígitos.']);

        $this->assertSame(0, Cliente::query()->count());
    }

    public function test_formulario_de_edicao_vem_preenchido(): void
    {
        $cliente = Cliente::factory()->create([
            'nome' => 'Escola Saber',
        ]);

        $this->comoAdmin()->get("/painel/clientes/{$cliente->id}/editar")
            ->assertOk()
            ->assertSee('Editar Cliente')
            ->assertSee('value="Escola Saber"', false)
            ->assertSee('Inativar')
            ->assertSee(route('clientes.inativar', $cliente->id), false)
            ->assertDontSee('name="valor"', false)
            ->assertDontSee('name="periodicidade"', false);
    }

    public function test_editar_cliente_inexistente_volta_para_a_listagem(): void
    {
        $this->comoAdmin()->get('/painel/clientes/999/editar')
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHasErrors(['error' => 'Cliente não encontrado.']);
    }

    public function test_atualiza_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->comoAdmin()->put("/painel/clientes/{$cliente->id}", [
            'nome' => 'Nome Novo',
            'email' => 'novo@exemplo.com',
            'nome_contato' => 'Contato Novo',
        ]);

        $response->assertRedirect(route('clientes.show', $cliente->id));
        $response->assertSessionHas('success', 'Cliente atualizado com sucesso!');

        $cliente->refresh();
        $this->assertSame('Nome Novo', $cliente->nome);
        $this->assertSame('novo@exemplo.com', $cliente->email);
        $this->assertSame('Contato Novo', $cliente->nome_contato);
    }

    public function test_nao_exclui_cliente_com_assinatura(): void
    {
        $cliente = Cliente::factory()->create();
        Assinatura::factory()->create(['cliente_id' => $cliente->id]);

        $this->comoAdmin()->delete("/painel/clientes/{$cliente->id}")
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHasErrors(['error' => 'Este cliente tem assinaturas e não pode ser excluído. O histórico de cobrança fica nas assinaturas.']);

        $this->assertModelExists($cliente);
    }

    public function test_atualizar_cliente_inexistente_volta_para_a_listagem(): void
    {
        $this->comoAdmin()->put('/painel/clientes/999', $this->dadosValidos())
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHasErrors(['error' => 'Cliente não encontrado.']);
    }

    public function test_deleta_cliente(): void
    {
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()->delete("/painel/clientes/{$cliente->id}")
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('success', 'Cliente deletado com sucesso!');

        $this->assertModelMissing($cliente);
    }

    public function test_deletar_cliente_inexistente_avisa(): void
    {
        $this->comoAdmin()->delete('/painel/clientes/999')
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHasErrors(['error' => 'Cliente não encontrado.']);
    }

    #[DataProvider('acoesProtegidas')]
    public function test_usuario_sem_permissao_recebe_403(string $metodo, string $uri, string $permissaoQueFalta): void
    {
        $cliente = Cliente::factory()->create(['nome' => 'Cliente Original']);

        $todasDeClientes = ['cliente_visualizar', 'cliente_cadastrar', 'cliente_editar', 'cliente_deletar'];
        $permissoes = array_values(array_diff($todasDeClientes, [$permissaoQueFalta]));

        $this->comoUsuarioCom($permissoes)
            ->call($metodo, str_replace('{id}', (string) $cliente->id, $uri), $this->dadosValidos())
            ->assertForbidden();

        $this->assertSame(1, Cliente::query()->count());
        $this->assertSame('Cliente Original', $cliente->fresh()->nome);
        $this->assertSame(SituacaoCliente::Ativo, $cliente->fresh()->situacao);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function acoesProtegidas(): array
    {
        return [
            'listar' => ['GET', '/painel/clientes', 'cliente_visualizar'],
            'abrir cadastro' => ['GET', '/painel/clientes/criar', 'cliente_cadastrar'],
            'cadastrar' => ['POST', '/painel/clientes', 'cliente_cadastrar'],
            'abrir edição' => ['GET', '/painel/clientes/{id}/editar', 'cliente_editar'],
            'atualizar' => ['PUT', '/painel/clientes/{id}', 'cliente_editar'],
            'deletar' => ['DELETE', '/painel/clientes/{id}', 'cliente_deletar'],
            'inativar' => ['POST', '/painel/clientes/{id}/inativar', 'cliente_editar'],
            'reativar' => ['POST', '/painel/clientes/{id}/reativar', 'cliente_editar'],
        ];
    }

    public function test_usuario_que_so_visualiza_nao_ve_botoes_de_acao(): void
    {
        $cliente = Cliente::factory()->create();

        $response = $this->comoUsuarioCom(['cliente_visualizar'])->get('/painel/clientes');

        $response->assertOk();
        $response->assertSee($cliente->nome);
        $response->assertSee(route('clientes.show', $cliente->id), false);
        $response->assertSee('Assinaturas');
        $response->assertDontSee('Novo Cliente');
        $response->assertDontSee('Editar');
        $response->assertDontSee('Deletar');
        $response->assertDontSee('Inativar');
        $response->assertDontSee(route('clientes.edit', $cliente->id), false);
    }

    public function test_menu_lateral_mostra_clientes_para_quem_pode_visualizar(): void
    {
        $this->comoUsuarioCom(['usuario_visualizar', 'cliente_visualizar'])->get('/painel/usuarios')
            ->assertOk()
            ->assertSee(route('clientes.index'), false);
    }

    public function test_menu_lateral_esconde_clientes_de_quem_nao_pode_visualizar(): void
    {
        $this->comoUsuarioCom(['usuario_visualizar'])->get('/painel/usuarios')
            ->assertOk()
            ->assertDontSee(route('clientes.index'), false);
    }

    public function test_id_que_nao_e_numero_nao_encontra_a_rota(): void
    {
        $this->comoAdmin()->get('/painel/clientes/abc/editar')->assertNotFound();
    }

    public function test_listagem_filtra_clientes_inativos(): void
    {
        Cliente::factory()->create(['nome' => 'Cliente Ativo']);
        Cliente::factory()->inativo()->create(['nome' => 'Cliente Encerrado']);

        $this->comoAdmin()->get('/painel/clientes?situacao=inativo')
            ->assertOk()
            ->assertSee('Cliente Encerrado')
            ->assertSee('Inativo')
            ->assertSee('Reativar')
            ->assertDontSee('Cliente Ativo');
    }

    public function test_inativar_encerra_assinaturas_e_cancela_lancamentos_futuros(): void
    {
        $this->travelTo('2031-06-15 15:00:00');

        $cliente = Cliente::factory()->create();
        $ativa = Assinatura::factory()->mensal()->create([
            'cliente_id' => $cliente->id,
            'data_inicio' => '2031-05-01',
        ]);
        $jaEncerrada = Assinatura::factory()->encerrada()->create([
            'cliente_id' => $cliente->id,
            'data_inicio' => '2030-01-01',
            'data_fim' => '2030-12-31',
        ]);
        $pago = Lancamento::factory()->pago()->create([
            'assinatura_id' => $ativa->id,
            'competencia' => '2031-05-01',
            'vencimento' => '2031-05-10',
        ]);
        $emAberto = Lancamento::factory()->create([
            'assinatura_id' => $ativa->id,
            'competencia' => '2031-06-01',
            'vencimento' => '2031-06-10',
        ]);
        $futuro = Lancamento::factory()->create([
            'assinatura_id' => $ativa->id,
            'competencia' => '2031-07-01',
            'vencimento' => '2031-07-10',
        ]);

        $this->comoAdmin()
            ->post(route('clientes.inativar', $cliente))
            ->assertRedirect(route('clientes.show', $cliente))
            ->assertSessionHas('success', 'Cliente inativado. A assinatura ativa foi encerrada e os lançamentos com vencimento posterior foram cancelados.');

        $this->assertSame(SituacaoCliente::Inativo, $cliente->fresh()->situacao);
        $this->assertSame(SituacaoAssinatura::Encerrada, $ativa->fresh()->situacao);
        $this->assertSame('2031-06-15', $ativa->fresh()->data_fim->toDateString());
        $this->assertSame(SituacaoAssinatura::Encerrada, $jaEncerrada->fresh()->situacao);
        $this->assertSame('2030-12-31', $jaEncerrada->fresh()->data_fim->toDateString());
        $this->assertSame(SituacaoLancamento::Pago, $pago->fresh()->situacao);
        $this->assertSame(SituacaoLancamento::Aberto, $emAberto->fresh()->situacao);
        $this->assertSame(SituacaoLancamento::Cancelado, $futuro->fresh()->situacao);
    }

    public function test_inativar_antes_do_inicio_usa_a_data_de_inicio(): void
    {
        $this->travelTo('2031-06-15 15:00:00');

        $cliente = Cliente::factory()->create();
        $assinatura = Assinatura::factory()->mensal()->create([
            'cliente_id' => $cliente->id,
            'data_inicio' => '2031-08-01',
        ]);
        $lancamento = Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-08-01',
            'vencimento' => '2031-08-10',
        ]);

        $this->comoAdmin()
            ->post(route('clientes.inativar', $cliente))
            ->assertRedirect(route('clientes.show', $cliente));

        $this->assertSame('2031-08-01', $assinatura->fresh()->data_fim->toDateString());
        $this->assertSame(SituacaoLancamento::Cancelado, $lancamento->fresh()->situacao);
    }

    public function test_reativar_nao_reabre_a_assinatura(): void
    {
        $cliente = Cliente::factory()->inativo()->create();
        $assinatura = Assinatura::factory()->encerrada()->create(['cliente_id' => $cliente->id]);

        $this->comoAdmin()
            ->post(route('clientes.reativar', $cliente))
            ->assertRedirect(route('clientes.show', $cliente))
            ->assertSessionHas('success', 'Cliente reativado. As assinaturas encerradas continuam encerradas; cadastre uma nova se a cobrança voltar.');

        $this->assertSame(SituacaoCliente::Ativo, $cliente->fresh()->situacao);
        $this->assertSame(SituacaoAssinatura::Encerrada, $assinatura->fresh()->situacao);
    }

    public function test_ficha_de_cliente_inativo_nao_oferece_nova_assinatura(): void
    {
        $cliente = Cliente::factory()->inativo()->create(['nome' => 'Cliente Parado']);

        $this->comoAdmin()->get(route('clientes.show', $cliente))
            ->assertOk()
            ->assertSee('Cliente Parado')
            ->assertSee('Inativo')
            ->assertSee('Reativar')
            ->assertDontSee('Nova Assinatura')
            ->assertDontSee('>Inativar<', false);
    }

    public function test_quem_so_visualiza_nao_ve_o_botao_de_inativar(): void
    {
        $cliente = Cliente::factory()->create();

        $this->comoUsuarioCom(['cliente_visualizar'])
            ->get(route('clientes.show', $cliente))
            ->assertOk()
            ->assertSee('Ativo')
            ->assertDontSee('Inativar')
            ->assertDontSee('Reativar');
    }

    public function test_nao_cadastra_assinatura_para_cliente_inativo(): void
    {
        $cliente = Cliente::factory()->inativo()->create();

        $this->comoAdmin()
            ->post(route('assinaturas.store', $cliente), [
                'descricao' => 'Mensalidade',
                'valor' => '150,00',
                'periodicidade' => 'mensal',
                'dia_vencimento' => '10',
                'dia_geracao' => '5',
                'data_inicio' => '2031-04-10',
            ])
            ->assertRedirect(route('clientes.show', $cliente))
            ->assertSessionHasErrors(['error' => 'Cliente inativo não recebe assinatura nova. Reative o cliente antes.']);

        $this->assertSame(0, Assinatura::query()->count());
    }

    /**
     * Autentica o admin do seeder com as permissões já carregadas na sessão,
     * como acontece depois que ele passa pelo dashboard.
     */
    private function comoAdmin(): static
    {
        $admin = Usuario::query()->where('login', 'admin')->firstOrFail();

        return $this->actingAs($admin, 'painel')
            ->withSession(['permissoes' => $admin->acessos()]);
    }

    /**
     * Autentica um usuário cujo único grupo tem exatamente as permissões informadas.
     *
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

    private function criarClientesNumerados(int $quantidade): void
    {
        foreach (range(1, $quantidade) as $numero) {
            Cliente::factory()->create(['nome' => sprintf('Cliente %02d', $numero)]);
        }
    }

    /**
     * @param  array<string, string>  $sobrescrever
     * @return array<string, string>
     */
    private function dadosValidos(array $sobrescrever = []): array
    {
        return array_merge([
            'nome' => 'Cliente Teste',
            'email' => 'cliente@exemplo.com',
            'nome_contato' => 'Fulano',
        ], $sobrescrever);
    }
}
