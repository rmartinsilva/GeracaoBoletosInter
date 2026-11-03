<?php

namespace Tests\Feature;

use App\Enums\Periodicidade;
use App\Enums\SituacaoAssinatura;
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

class PainelAssinaturaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissoesSeeder::class);
    }

    public function test_ficha_lista_a_assinatura_e_o_atalho_para_os_lancamentos(): void
    {
        $cliente = Cliente::factory()->create(['nome' => 'Padaria Pão Quente']);
        $assinatura = Assinatura::factory()->mensal()->create([
            'cliente_id' => $cliente->id,
            'descricao' => 'Mensalidade do sistema',
            'valor' => 1234.5,
            'data_inicio' => '2031-04-10',
        ]);

        $this->comoAdmin()->get(route('clientes.show', $cliente->id))
            ->assertOk()
            ->assertSee('Padaria Pão Quente')
            ->assertSee('Mensalidade do sistema')
            ->assertSee('R$ 1.234,50')
            ->assertSee('Mensal')
            ->assertSee('Nova Assinatura')
            ->assertSee(route('assinaturas.show', [$cliente->id, $assinatura->id]), false);
    }

    public function test_formulario_de_assinatura_lista_as_periodicidades(): void
    {
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()->get(route('assinaturas.create', $cliente))
            ->assertOk()
            ->assertSee('Nova Assinatura')
            ->assertSee('Dia de geração')
            ->assertSee('value="mensal"', false)
            ->assertSee('value="anual"', false)
            ->assertDontSee('value="avulso"', false);
    }

    public function test_cadastra_assinatura_mensal_e_gera_lancamentos_ate_o_mes_atual(): void
    {
        $this->travelTo('2031-06-15 15:00:00');
        $cliente = Cliente::factory()->create();

        $response = $this->comoAdmin()->post(route('assinaturas.store', $cliente), [
            'descricao' => 'Mensalidade do sistema',
            'valor' => 'R$ 1.234,56',
            'periodicidade' => 'mensal',
            'dia_vencimento' => '10',
            'dia_geracao' => '5',
            'data_inicio' => '2031-04-20',
        ]);

        $assinatura = Assinatura::query()->sole();

        $response->assertRedirect(route('assinaturas.show', [$cliente->id, $assinatura->id]));
        $response->assertSessionHas('success', 'Assinatura cadastrada com sucesso! Foram gerados 2 lançamentos.');
        $this->assertSame('1234.56', $assinatura->valor);
        $this->assertSame(Periodicidade::Mensal, $assinatura->periodicidade);
        $this->assertSame(10, $assinatura->dia_vencimento);
        $this->assertSame(5, $assinatura->dia_geracao);
        $this->assertSame(SituacaoAssinatura::Ativa, $assinatura->situacao);

        $linhas = $assinatura->lancamentos()->orderBy('competencia')->get();
        $this->assertCount(2, $linhas);
        $this->assertSame('2031-05-01', $linhas[0]->competencia->toDateString());
        $this->assertSame('2031-05-10', $linhas[0]->vencimento->toDateString());
        $this->assertSame('1234.56', $linhas[0]->valor);
        $this->assertSame(SituacaoLancamento::Aberto, $linhas[0]->situacao);
        $this->assertSame('2031-06-10', $linhas[1]->vencimento->toDateString());
    }

    public function test_cadastra_assinatura_anual_com_um_lancamento_por_ano(): void
    {
        $this->travelTo('2031-06-15 15:00:00');
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()
            ->post(route('assinaturas.store', $cliente), $this->dadosValidos([
                'periodicidade' => 'anual',
                'data_inicio' => '2029-06-10',
            ]))
            ->assertSessionHas('success', 'Assinatura cadastrada com sucesso! Foram gerados 3 lançamentos.');

        $vencimentos = Assinatura::query()->sole()->lancamentos()->orderBy('competencia')->pluck('vencimento');

        $this->assertSame([
            '2029-06-10',
            '2030-06-10',
            '2031-06-10',
        ], $vencimentos->map(fn ($data) => $data->toDateString())->all());
    }

    #[DataProvider('valoresAceitos')]
    public function test_cadastro_aceita_o_valor_no_formato_brasileiro_ou_numerico(string $digitado, string $esperado): void
    {
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()
            ->post(route('assinaturas.store', $cliente), $this->dadosValidos(['valor' => $digitado]))
            ->assertSessionHasNoErrors();

        $this->assertSame($esperado, Assinatura::query()->sole()->valor);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function valoresAceitos(): array
    {
        return [
            'centavos com vírgula' => ['99,90', '99.90'],
            'milhar e centavos' => ['1.500,00', '1500.00'],
            'milhões' => ['1.234.567,89', '1234567.89'],
            'milhar sem centavos' => ['1.500', '1500.00'],
            'inteiro' => ['1500', '1500.00'],
            'ponto decimal' => ['1500.5', '1500.50'],
            'com símbolo da moeda' => ['R$ 250,00', '250.00'],
        ];
    }

    public function test_cadastro_exige_os_campos_da_assinatura(): void
    {
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()
            ->post(route('assinaturas.store', $cliente), [])
            ->assertSessionHasErrors([
                'descricao' => 'A descrição é obrigatória.',
                'valor' => 'O valor é obrigatório.',
                'periodicidade' => 'A periodicidade é obrigatória.',
                'data_inicio' => 'A data de início é obrigatória.',
            ]);

        $this->assertSame(0, Assinatura::query()->count());
    }

    #[DataProvider('dadosInvalidos')]
    public function test_cadastro_rejeita_dados_invalidos(string $campo, string $valor, string $mensagem): void
    {
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()
            ->post(route('assinaturas.store', $cliente), $this->dadosValidos([$campo => $valor]))
            ->assertSessionHasErrors([$campo => $mensagem]);

        $this->assertSame(0, Assinatura::query()->count());
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function dadosInvalidos(): array
    {
        return [
            'valor zero' => ['valor', '0,00', 'O valor deve ser maior que zero.'],
            'valor negativo' => ['valor', '-10,00', 'O valor deve ser maior que zero.'],
            'valor com texto' => ['valor', 'mil reais', 'Informe um valor válido, por exemplo 1.500,00.'],
            'valor com três casas' => ['valor', '10,999', 'O valor deve ter no máximo duas casas decimais.'],
            'periodicidade desconhecida' => ['periodicidade', 'semanal', 'Escolha uma periodicidade válida: mensal ou anual.'],
            'dia zero' => ['dia_vencimento', '0', 'O dia de vencimento deve ser um número de 1 a 31.'],
            'dia 32' => ['dia_vencimento', '32', 'O dia de vencimento deve ser um número de 1 a 31.'],
        ];
    }

    public function test_mensal_sem_dia_de_vencimento_e_rejeitada(): void
    {
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()
            ->post(route('assinaturas.store', $cliente), $this->dadosValidos(['dia_vencimento' => '']))
            ->assertSessionHasErrors(['dia_vencimento' => 'Informe o dia de vencimento.']);

        $this->assertSame(0, Assinatura::query()->count());
    }

    public function test_mensal_sem_dia_de_geracao_e_rejeitada(): void
    {
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()
            ->post(route('assinaturas.store', $cliente), $this->dadosValidos(['dia_geracao' => '']))
            ->assertSessionHasErrors(['dia_geracao' => 'Informe o dia de geração da cobrança.']);

        $this->assertSame(0, Assinatura::query()->count());
    }

    public function test_historico_mostra_vencido_e_em_aberto(): void
    {
        $this->travelTo('2031-06-15 15:00:00');
        $assinatura = Assinatura::factory()->create(['valor' => 150, 'dia_vencimento' => 10]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-05-01',
            'vencimento' => '2031-05-10',
            'valor' => 150,
        ]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-07-01',
            'vencimento' => '2031-07-10',
            'valor' => 150,
        ]);

        $this->comoAdmin()->get(route('assinaturas.show', [$assinatura->cliente_id, $assinatura->id]))
            ->assertOk()
            ->assertSee('05/2031')
            ->assertSee('10/05/2031')
            ->assertSee('R$ 150,00')
            ->assertSee('Vencido')
            ->assertSee('Em aberto')
            ->assertSee('Geração no dia 5')
            ->assertSee('Registrar pagamento')
            ->assertSee('Gerar próximo lançamento');
    }

    public function test_gerar_proximo_lancamento_usa_o_valor_atual_da_assinatura(): void
    {
        $assinatura = Assinatura::factory()->create(['valor' => 200, 'dia_vencimento' => 10, 'data_inicio' => '2031-04-10']);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-06-01',
            'vencimento' => '2031-06-10',
            'valor' => 100,
        ]);

        $this->comoAdmin()
            ->post(route('lancamentos.store', [$assinatura->cliente_id, $assinatura->id]))
            ->assertRedirect(route('assinaturas.show', [$assinatura->cliente_id, $assinatura->id]))
            ->assertSessionHas('success', 'Lançamento de 07/2031 gerado.');

        $linhas = $assinatura->lancamentos()->orderBy('competencia')->get();
        $this->assertCount(2, $linhas);
        $this->assertSame('100.00', $linhas[0]->valor);
        $this->assertSame('2031-07-10', $linhas[1]->vencimento->toDateString());
        $this->assertSame('200.00', $linhas[1]->valor);
    }

    public function test_registra_pagamento(): void
    {
        $this->travelTo('2031-06-15 15:00:00');
        $lancamento = Lancamento::factory()->create();

        $this->comoAdmin()
            ->post(route('lancamentos.pagamento', [$lancamento->assinatura->cliente_id, $lancamento->assinatura_id, $lancamento->id]), [
                'data_pagamento' => '2031-05-10',
                'lancamento_id' => $lancamento->id,
            ])
            ->assertSessionHas('success', 'Pagamento registrado.');

        $lancamento->refresh();
        $this->assertSame(SituacaoLancamento::Pago, $lancamento->situacao);
        $this->assertSame('2031-05-10 03:00:00', $lancamento->pago_em->format('Y-m-d H:i:s'));
    }

    public function test_rejeita_pagamento_futuro(): void
    {
        $this->travelTo('2031-06-15 15:00:00');
        $lancamento = Lancamento::factory()->create();

        $this->comoAdmin()
            ->post(route('lancamentos.pagamento', [$lancamento->assinatura->cliente_id, $lancamento->assinatura_id, $lancamento->id]), [
                'data_pagamento' => '2031-06-16',
            ])
            ->assertSessionHasErrors(['data_pagamento' => 'A data do pagamento não pode ser futura.']);

        $this->assertSame(SituacaoLancamento::Aberto, $lancamento->fresh()->situacao);
    }

    public function test_cancela_lancamento_em_aberto(): void
    {
        $lancamento = Lancamento::factory()->create();

        $this->comoAdmin()
            ->post(route('lancamentos.cancelar', [$lancamento->assinatura->cliente_id, $lancamento->assinatura_id, $lancamento->id]))
            ->assertSessionHas('success', 'Lançamento cancelado.');

        $this->assertSame(SituacaoLancamento::Cancelado, $lancamento->fresh()->situacao);
    }

    public function test_ajusta_valor_e_vencimento_de_lancamento_em_aberto(): void
    {
        $lancamento = Lancamento::factory()->create(['valor' => 150, 'vencimento' => '2031-05-10']);

        $this->comoAdmin()
            ->put(route('lancamentos.update', [$lancamento->assinatura->cliente_id, $lancamento->assinatura_id, $lancamento->id]), [
                'valor' => '80,00',
                'vencimento' => '2031-05-12',
                'lancamento_id' => $lancamento->id,
            ])
            ->assertSessionHas('success', 'Lançamento atualizado.');

        $lancamento->refresh();
        $this->assertSame('80.00', $lancamento->valor);
        $this->assertSame('2031-05-12', $lancamento->vencimento->toDateString());
    }

    public function test_nao_ajusta_lancamento_pago(): void
    {
        $lancamento = Lancamento::factory()->pago()->create(['valor' => 150]);

        $this->comoAdmin()
            ->put(route('lancamentos.update', [$lancamento->assinatura->cliente_id, $lancamento->assinatura_id, $lancamento->id]), [
                'valor' => '80,00',
                'vencimento' => '2031-05-12',
            ])
            ->assertSessionHasErrors(['error' => 'Só é possível ajustar um lançamento em aberto.']);

        $this->assertSame('150.00', $lancamento->fresh()->valor);
    }

    public function test_atualizar_valor_da_assinatura_nao_reescreve_lancamentos(): void
    {
        $assinatura = Assinatura::factory()->create(['valor' => 100, 'descricao' => 'Mensalidade', 'dia_vencimento' => 10]);
        $lancamento = Lancamento::factory()->create(['assinatura_id' => $assinatura->id, 'valor' => 100]);

        $this->comoAdmin()
            ->put(route('assinaturas.update', [$assinatura->cliente_id, $assinatura->id]), [
                'descricao' => 'Mensalidade reajustada',
                'valor' => '2.000,00',
                'dia_vencimento' => '10',
                'dia_geracao' => '5',
                'situacao' => 'ativa',
            ])
            ->assertSessionHas('success', 'Assinatura atualizada com sucesso!');

        $this->assertSame('2000.00', $assinatura->fresh()->valor);
        $this->assertSame('100.00', $lancamento->fresh()->valor);
    }

    public function test_encerrar_cancela_lancamentos_em_aberto_depois_da_data(): void
    {
        $assinatura = Assinatura::factory()->create(['dia_vencimento' => 10, 'data_inicio' => '2031-01-10']);
        $passado = Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-05-01',
            'vencimento' => '2031-05-10',
        ]);
        $futuro = Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-07-01',
            'vencimento' => '2031-07-10',
        ]);

        $this->comoAdmin()
            ->put(route('assinaturas.update', [$assinatura->cliente_id, $assinatura->id]), [
                'descricao' => $assinatura->descricao,
                'valor' => '150,00',
                'dia_vencimento' => '10',
                'dia_geracao' => '5',
                'situacao' => 'encerrada',
                'data_fim' => '2031-06-30',
            ])
            ->assertSessionHasNoErrors();

        $assinatura->refresh();
        $this->assertSame(SituacaoAssinatura::Encerrada, $assinatura->situacao);
        $this->assertSame('2031-06-30', $assinatura->data_fim->toDateString());
        $this->assertSame(SituacaoLancamento::Aberto, $passado->fresh()->situacao);
        $this->assertSame(SituacaoLancamento::Cancelado, $futuro->fresh()->situacao);
    }

    public function test_nao_exclui_assinatura_com_pagamento(): void
    {
        $lancamento = Lancamento::factory()->pago()->create();
        $assinatura = $lancamento->assinatura;

        $this->comoAdmin()
            ->delete(route('assinaturas.destroy', [$assinatura->cliente_id, $assinatura->id]))
            ->assertSessionHasErrors(['error' => 'Esta assinatura tem pagamentos registrados e não pode ser excluída. Encerre-a para manter o histórico.']);

        $this->assertModelExists($assinatura);
        $this->assertModelExists($lancamento);
    }

    public function test_exclui_assinatura_sem_pagamento_e_os_lancamentos(): void
    {
        $lancamento = Lancamento::factory()->create();
        $assinatura = $lancamento->assinatura;

        $this->comoAdmin()
            ->delete(route('assinaturas.destroy', [$assinatura->cliente_id, $assinatura->id]))
            ->assertRedirect(route('clientes.show', $assinatura->cliente_id))
            ->assertSessionHas('success', 'Assinatura excluída com sucesso!');

        $this->assertModelMissing($assinatura);
        $this->assertModelMissing($lancamento);
    }

    public function test_assinatura_de_outro_cliente_nao_abre(): void
    {
        $assinatura = Assinatura::factory()->create();
        $outro = Cliente::factory()->create();

        $this->comoAdmin()
            ->get(route('assinaturas.show', [$outro->id, $assinatura->id]))
            ->assertRedirect(route('clientes.show', $outro->id))
            ->assertSessionHasErrors(['error' => 'Assinatura não encontrada.']);
    }

    public function test_historico_esconde_a_exclusao_quando_ha_pagamento(): void
    {
        $lancamento = Lancamento::factory()->pago()->create();
        $assinatura = $lancamento->assinatura;

        $this->comoAdmin()
            ->get(route('assinaturas.show', [$assinatura->cliente_id, $assinatura->id]))
            ->assertOk()
            ->assertSee('Há pagamentos registrados. Encerre a assinatura para manter o histórico.')
            ->assertDontSee('Excluir assinatura');
    }

    #[DataProvider('acoesProtegidas')]
    public function test_usuario_sem_permissao_recebe_403(string $metodo, string $uri, string $permissaoQueFalta): void
    {
        $assinatura = Assinatura::factory()->create();
        $lancamento = Lancamento::factory()->create(['assinatura_id' => $assinatura->id]);

        $permissoes = array_values(array_diff(
            ['cliente_visualizar', 'cliente_cadastrar', 'cliente_editar', 'cliente_deletar'],
            [$permissaoQueFalta],
        ));

        $uri = str_replace(
            ['{cliente}', '{assinatura}', '{lancamento}'],
            [(string) $assinatura->cliente_id, (string) $assinatura->id, (string) $lancamento->id],
            $uri,
        );

        $this->comoUsuarioCom($permissoes)
            ->call($metodo, $uri, $this->dadosValidos())
            ->assertForbidden();
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function acoesProtegidas(): array
    {
        return [
            'abrir cadastro' => ['GET', '/painel/clientes/{cliente}/assinaturas/criar', 'cliente_cadastrar'],
            'cadastrar' => ['POST', '/painel/clientes/{cliente}/assinaturas', 'cliente_cadastrar'],
            'ver lançamentos' => ['GET', '/painel/clientes/{cliente}/assinaturas/{assinatura}', 'cliente_visualizar'],
            'abrir edição' => ['GET', '/painel/clientes/{cliente}/assinaturas/{assinatura}/editar', 'cliente_editar'],
            'atualizar' => ['PUT', '/painel/clientes/{cliente}/assinaturas/{assinatura}', 'cliente_editar'],
            'excluir' => ['DELETE', '/painel/clientes/{cliente}/assinaturas/{assinatura}', 'cliente_deletar'],
            'gerar lançamento' => ['POST', '/painel/clientes/{cliente}/assinaturas/{assinatura}/lancamentos', 'cliente_editar'],
            'registrar pagamento' => ['POST', '/painel/clientes/{cliente}/assinaturas/{assinatura}/lancamentos/{lancamento}/pagamento', 'cliente_editar'],
        ];
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

    /**
     * @param  array<string, string>  $sobrescrever
     * @return array<string, string>
     */
    private function dadosValidos(array $sobrescrever = []): array
    {
        return array_merge([
            'descricao' => 'Mensalidade',
            'valor' => '150,00',
            'periodicidade' => 'mensal',
            'dia_vencimento' => '10',
            'dia_geracao' => '5',
            'data_inicio' => '2031-04-10',
            'situacao' => 'ativa',
            'data_pagamento' => '2020-01-10',
            'vencimento' => '2031-05-10',
        ], $sobrescrever);
    }
}
