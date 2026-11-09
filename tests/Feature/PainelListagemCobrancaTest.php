<?php

namespace Tests\Feature;

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

class PainelListagemCobrancaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissoesSeeder::class);
    }

    public function test_visitante_e_redirecionado_para_o_login(): void
    {
        $this->get('/painel/cobrancas')->assertRedirect(route('login'));
    }

    public function test_usuario_sem_permissao_recebe_403(): void
    {
        $this->comoUsuarioCom(['usuario_visualizar'])
            ->get('/painel/cobrancas')
            ->assertForbidden();
    }

    public function test_lista_assinatura_e_avulsa_do_periodo_com_baixa_e_resumo(): void
    {
        $this->travelTo('2031-05-15 15:00:00');

        $padaria = Cliente::factory()->create(['nome' => 'Padaria Pão Quente']);
        $assinatura = Assinatura::factory()->mensal()->create([
            'cliente_id' => $padaria->id,
            'descricao' => 'Mensalidade do sistema',
        ]);
        Lancamento::factory()->pago()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-05-01',
            'vencimento' => '2031-05-10',
            'valor' => 1500.50,
            'pago_em' => '2031-05-10 15:30:00',
            'codigo_solicitacao' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaa1',
            'email_enviado_em' => '2031-05-05 12:00:00',
            'nosso_numero' => '998877',
        ]);

        $oficina = Cliente::factory()->create(['nome' => 'Oficina do Zé']);
        Lancamento::factory()->create([
            'assinatura_id' => null,
            'cliente_id' => $oficina->id,
            'descricao' => 'Taxa de adesão',
            'competencia' => '2031-05-20',
            'vencimento' => '2031-05-20',
            'valor' => 80,
            'codigo_solicitacao' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaa2',
        ]);

        $mercado = Cliente::factory()->create(['nome' => 'Mercado Central']);
        $assinaturaMercado = Assinatura::factory()->mensal()->create([
            'cliente_id' => $mercado->id,
            'descricao' => 'Mensalidade do mercado',
        ]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinaturaMercado->id,
            'competencia' => '2031-05-01',
            'vencimento' => '2031-05-01',
            'valor' => 200,
        ]);

        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-05-12',
            'vencimento' => '2031-05-12',
            'valor' => 50,
            'situacao' => SituacaoLancamento::Cancelado,
        ]);

        $falha = Cliente::factory()->create(['nome' => 'Cliente com Falha']);
        $assinaturaFalha = Assinatura::factory()->mensal()->create([
            'cliente_id' => $falha->id,
            'descricao' => 'Mensalidade com falha',
        ]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinaturaFalha->id,
            'competencia' => '2031-05-08',
            'vencimento' => '2031-05-08',
            'valor' => 30,
            'erro_emissao' => 'Banco indisponível',
        ]);

        $fora = Cliente::factory()->create(['nome' => 'Cliente de Junho']);
        $assinaturaFora = Assinatura::factory()->mensal()->create(['cliente_id' => $fora->id]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinaturaFora->id,
            'competencia' => '2031-06-01',
            'vencimento' => '2031-06-05',
            'valor' => 999,
        ]);

        $response = $this->comoAdmin()->get('/painel/cobrancas?de=2031-05-01&ate=2031-05-31');

        $response->assertOk();
        $response->assertSee('Cobranças do período');
        $response->assertSee('Padaria Pão Quente');
        $response->assertSee('Mensalidade do sistema');
        $response->assertSee('Oficina do Zé');
        $response->assertSee('Taxa de adesão');
        $response->assertSee('Avulsa');
        $response->assertSee('Mercado Central');
        $response->assertSee('Cliente com Falha');
        $response->assertSee('Banco indisponível');
        $response->assertSee('E-mail enviado');
        $response->assertSee('Aguardando boleto');
        $response->assertSee('Sem boleto');
        $response->assertSee('Nosso número 998877');
        $response->assertSee('10/05/2031 12:30');
        $response->assertSee('R$ 1.500,50');
        $response->assertSee('R$ 1.860,50');
        $response->assertSee('R$ 310,00');
        $response->assertSee('2 emitidas · 1 cancelada');
        $response->assertSee('Pago');
        $response->assertSee('Vencido');
        $response->assertSee('Cancelado');
        $response->assertSee('Em aberto');
        $response->assertSee(route('assinaturas.show', [$padaria->id, $assinatura->id]), false);
        $response->assertSee(route('clientes.show', $oficina->id), false);
        $response->assertSee(route('cobrancas.index'), false);
        $response->assertDontSee('Cliente de Junho');
        $response->assertDontSee('R$ 999,00');
    }

    public function test_periodo_padrao_e_o_mes_atual_em_brasilia(): void
    {
        $this->travelTo('2031-12-01 02:00:00');

        $cliente = Cliente::factory()->create(['nome' => 'Cliente de Novembro']);
        $assinatura = Assinatura::factory()->mensal()->create(['cliente_id' => $cliente->id]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-11-01',
            'vencimento' => '2031-11-15',
        ]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-12-01',
            'vencimento' => '2031-12-05',
        ]);

        $response = $this->comoAdmin()->get('/painel/cobrancas');

        $response->assertOk();
        $response->assertSee('value="2031-11-01"', false);
        $response->assertSee('value="2031-11-30"', false);
        $response->assertSee('Cliente de Novembro');
        $response->assertSee('15/11/2031');
        $response->assertDontSee('05/12/2031');
    }

    public function test_data_invalida_volta_para_o_mes_atual(): void
    {
        $this->travelTo('2031-08-10 15:00:00');

        $this->comoAdmin()
            ->get('/painel/cobrancas?de=2031-02-31&ate=2031-08-20')
            ->assertOk()
            ->assertSee('value="2031-08-01"', false)
            ->assertSee('value="2031-08-20"', false);
    }

    public function test_periodo_invertido_e_reordenado(): void
    {
        $cliente = Cliente::factory()->create(['nome' => 'Cliente do Meio']);
        $assinatura = Assinatura::factory()->mensal()->create(['cliente_id' => $cliente->id]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-05-10',
            'vencimento' => '2031-05-10',
        ]);

        $this->comoAdmin()
            ->get('/painel/cobrancas?de=2031-05-31&ate=2031-05-01')
            ->assertOk()
            ->assertSee('value="2031-05-01"', false)
            ->assertSee('value="2031-05-31"', false)
            ->assertSee('Cliente do Meio');
    }

    #[DataProvider('buscas')]
    public function test_busca_por_cliente_ou_descricao(string $termo, string $visivel, string $oculto): void
    {
        $padaria = Cliente::factory()->create(['nome' => 'Padaria Pão Quente']);
        $assinatura = Assinatura::factory()->mensal()->create([
            'cliente_id' => $padaria->id,
            'descricao' => 'Mensalidade do sistema',
        ]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-05-01',
            'vencimento' => '2031-05-10',
        ]);

        $oficina = Cliente::factory()->create(['nome' => 'Oficina do Zé']);
        Lancamento::factory()->create([
            'assinatura_id' => null,
            'cliente_id' => $oficina->id,
            'descricao' => 'Taxa de adesão',
            'competencia' => '2031-05-20',
            'vencimento' => '2031-05-20',
        ]);

        $this->comoAdmin()
            ->get('/painel/cobrancas?de=2031-05-01&ate=2031-05-31&busca='.urlencode($termo))
            ->assertOk()
            ->assertSee($visivel)
            ->assertDontSee($oculto);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function buscas(): array
    {
        return [
            'nome do cliente' => ['Oficina', 'Oficina do Zé', 'Padaria Pão Quente'],
            'descrição da assinatura' => ['Mensalidade do sistema', 'Padaria Pão Quente', 'Oficina do Zé'],
            'descrição da avulsa' => ['adesão', 'Taxa de adesão', 'Mensalidade do sistema'],
        ];
    }

    public function test_filtro_de_pagas_mostra_so_a_baixa_e_mantem_o_resumo_do_periodo(): void
    {
        $paga = Cliente::factory()->create(['nome' => 'Cliente Baixado']);
        $assinatura = Assinatura::factory()->mensal()->create(['cliente_id' => $paga->id]);
        Lancamento::factory()->pago()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-05-01',
            'vencimento' => '2031-05-10',
            'codigo_solicitacao' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbb1',
        ]);

        $aberta = Cliente::factory()->create(['nome' => 'Cliente em Aberto']);
        Lancamento::factory()->create([
            'assinatura_id' => null,
            'cliente_id' => $aberta->id,
            'descricao' => 'Cobrança ainda aberta',
            'competencia' => '2031-05-20',
            'vencimento' => '2031-05-20',
            'codigo_solicitacao' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbb2',
        ]);

        $this->comoAdmin()
            ->get('/painel/cobrancas?de=2031-05-01&ate=2031-05-31&situacao=pago')
            ->assertOk()
            ->assertSee('Cliente Baixado')
            ->assertSee('2 emitidas · 0 canceladas')
            ->assertDontSee('Cliente em Aberto');
    }

    public function test_filtro_de_vencidas_ignora_o_que_ainda_vai_vencer_e_o_que_ja_foi_pago(): void
    {
        $this->travelTo('2031-06-15 15:00:00');

        $vencido = Cliente::factory()->create(['nome' => 'Cliente Vencido']);
        $assinatura = Assinatura::factory()->mensal()->create(['cliente_id' => $vencido->id]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-06-01',
            'vencimento' => '2031-06-01',
        ]);

        $futuro = Cliente::factory()->create(['nome' => 'Cliente Futuro']);
        Lancamento::factory()->create([
            'assinatura_id' => null,
            'cliente_id' => $futuro->id,
            'descricao' => 'Ainda no prazo',
            'competencia' => '2031-06-20',
            'vencimento' => '2031-06-20',
        ]);

        $pago = Cliente::factory()->create(['nome' => 'Cliente Pago no Passado']);
        $assinaturaPaga = Assinatura::factory()->mensal()->create(['cliente_id' => $pago->id]);
        Lancamento::factory()->pago()->create([
            'assinatura_id' => $assinaturaPaga->id,
            'competencia' => '2031-06-02',
            'vencimento' => '2031-06-02',
        ]);

        $this->comoAdmin()
            ->get('/painel/cobrancas?de=2031-06-01&ate=2031-06-30&situacao=vencido')
            ->assertOk()
            ->assertSee('Cliente Vencido')
            ->assertDontSee('Cliente Futuro')
            ->assertDontSee('Cliente Pago no Passado');
    }

    public function test_filtro_de_falha_mostra_so_quem_nao_conseguiu_emitir(): void
    {
        $semBoleto = Cliente::factory()->create(['nome' => 'Cliente sem Boleto']);
        $assinatura = Assinatura::factory()->mensal()->create(['cliente_id' => $semBoleto->id]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-05-01',
            'vencimento' => '2031-05-10',
        ]);

        $falha = Cliente::factory()->create(['nome' => 'Cliente da Falha']);
        $assinaturaFalha = Assinatura::factory()->mensal()->create(['cliente_id' => $falha->id]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinaturaFalha->id,
            'competencia' => '2031-05-02',
            'vencimento' => '2031-05-11',
            'erro_emissao' => 'Certificado recusado',
        ]);

        $emitida = Cliente::factory()->create(['nome' => 'Cliente Emitido']);
        Lancamento::factory()->create([
            'assinatura_id' => null,
            'cliente_id' => $emitida->id,
            'descricao' => 'Boleto já emitido',
            'competencia' => '2031-05-12',
            'vencimento' => '2031-05-12',
            'codigo_solicitacao' => 'cccccccc-cccc-cccc-cccc-ccccccccccc1',
        ]);

        $this->comoAdmin()
            ->get('/painel/cobrancas?de=2031-05-01&ate=2031-05-31&emissao=falha')
            ->assertOk()
            ->assertSee('Cliente da Falha')
            ->assertSee('Certificado recusado')
            ->assertDontSee('Cliente sem Boleto')
            ->assertDontSee('Cliente Emitido');
    }

    public function test_filtro_sem_boleto_deixa_de_fora_falha_emissao_e_cancelada(): void
    {
        $semBoleto = Cliente::factory()->create(['nome' => 'Ainda sem boleto']);
        $assinatura = Assinatura::factory()->mensal()->create(['cliente_id' => $semBoleto->id]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-05-01',
            'vencimento' => '2031-05-10',
        ]);

        $falha = Cliente::factory()->create(['nome' => 'Falhou na emissão']);
        $assinaturaFalha = Assinatura::factory()->mensal()->create(['cliente_id' => $falha->id]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinaturaFalha->id,
            'competencia' => '2031-05-02',
            'vencimento' => '2031-05-11',
            'erro_emissao' => 'Timeout no banco',
        ]);

        $cancelada = Cliente::factory()->create(['nome' => 'Cobrança cancelada']);
        Lancamento::factory()->create([
            'assinatura_id' => null,
            'cliente_id' => $cancelada->id,
            'descricao' => 'Avulsa cancelada',
            'competencia' => '2031-05-12',
            'vencimento' => '2031-05-12',
            'situacao' => SituacaoLancamento::Cancelado,
        ]);

        $this->comoAdmin()
            ->get('/painel/cobrancas?de=2031-05-01&ate=2031-05-31&emissao=sem_boleto')
            ->assertOk()
            ->assertSee('Ainda sem boleto')
            ->assertDontSee('Falhou na emissão')
            ->assertDontSee('Cobrança cancelada');
    }

    public function test_listagem_vazia_mostra_aviso(): void
    {
        $this->comoAdmin()
            ->get('/painel/cobrancas?de=2031-05-01&ate=2031-05-31')
            ->assertOk()
            ->assertSee('Nenhuma cobrança neste período.');
    }

    public function test_segunda_pagina_traz_o_restante_do_periodo(): void
    {
        $cliente = Cliente::factory()->create(['nome' => 'Cliente Paginado']);
        $assinatura = Assinatura::factory()->mensal()->create(['cliente_id' => $cliente->id]);

        foreach (range(1, 21) as $dia) {
            Lancamento::factory()->create([
                'assinatura_id' => $assinatura->id,
                'competencia' => sprintf('2032-03-%02d', $dia),
                'vencimento' => sprintf('2032-03-%02d', $dia),
                'valor' => 10,
            ]);
        }

        $this->comoAdmin()
            ->get('/painel/cobrancas?de=2032-03-01&ate=2032-03-31&page=2')
            ->assertOk()
            ->assertSee('21/03/2032')
            ->assertSee('Página 2 de 2')
            ->assertSeeInOrder(['Mostrando', '21', 'até', '21', 'de', '21', 'resultados'])
            ->assertDontSee('02/03/2032');
    }

    public function test_menu_de_clientes_aponta_para_a_listagem(): void
    {
        $this->comoAdmin()
            ->get('/painel/clientes')
            ->assertOk()
            ->assertSee(route('cobrancas.index'), false)
            ->assertSee('Cobranças');
    }

    /**
     * Autentica o admin do seeder com as permissões já carregadas na sessão.
     */
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
