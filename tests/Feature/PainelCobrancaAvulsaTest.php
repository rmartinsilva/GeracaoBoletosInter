<?php

namespace Tests\Feature;

use App\Enums\SituacaoCliente;
use App\Mail\CobrancaEmitida;
use App\Models\Assinatura;
use App\Models\Cliente;
use App\Models\Lancamento;
use App\Models\Usuario;
use App\Services\Inter\ConfiguracaoInter;
use Database\Seeders\PermissoesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PainelCobrancaAvulsaTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://cdpj-sandbox.partners.uatinter.co';

    private const CODIGO = '497f6eca-6276-4993-bfeb-53cbbbba6f08';

    private string $certificado;

    private string $chave;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissoesSeeder::class);

        $certificado = tempnam(sys_get_temp_dir(), 'inter-cert');
        $chave = tempnam(sys_get_temp_dir(), 'inter-chave');

        if ($certificado === false || $chave === false) {
            $this->fail('Não foi possível criar os arquivos temporários do certificado.');
        }

        $this->certificado = $certificado;
        $this->chave = $chave;
        file_put_contents($this->certificado, 'certificado');
        file_put_contents($this->chave, 'chave');

        config([
            'inter.ambiente' => 'sandbox',
            'inter.url_base' => self::BASE,
            'inter.client_id' => 'cliente-teste',
            'inter.client_secret' => 'segredo-teste',
            'inter.certificado' => $this->certificado,
            'inter.chave' => $this->chave,
            'inter.certificado_senha' => null,
            'inter.conta_corrente' => null,
            'inter.escopos' => 'boleto-cobranca.read boleto-cobranca.write',
            'inter.timeout' => 15,
            'inter.margem_token_segundos' => 60,
        ]);

        $this->app->forgetInstance(ConfiguracaoInter::class);
        Cache::flush();
        Http::preventStrayRequests();
        Mail::fake();
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        @unlink($this->certificado);
        @unlink($this->chave);

        parent::tearDown();
    }

    public function test_formulario_pede_so_descricao_valor_e_vencimento(): void
    {
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()->get(route('cobrancas-avulsas.create', $cliente))
            ->assertOk()
            ->assertSee('Nova cobrança avulsa')
            ->assertSee('name="descricao"', false)
            ->assertSee('name="valor"', false)
            ->assertSee('data-mascara="dinheiro"', false)
            ->assertSee('name="vencimento"', false)
            ->assertDontSee('name="data_inicio"', false)
            ->assertDontSee('name="dia_geracao"', false)
            ->assertDontSee('name="periodicidade"', false);
    }

    public function test_salvar_emite_o_boleto_na_hora_sem_criar_assinatura(): void
    {
        $this->travelTo('2031-06-15 15:00:00');
        $cliente = Cliente::factory()->comPagador()->create(['nome' => 'Padaria Sol', 'email' => 'padaria@exemplo.com']);

        Http::fake([
            self::BASE.'/oauth/v2/token' => Http::response(['access_token' => 'token-teste', 'expires_in' => 3600]),
            self::BASE.'/cobranca/v3/cobrancas/'.self::CODIGO.'/pdf' => Http::response(['pdf' => base64_encode('%PDF-1.4')]),
            self::BASE.'/cobranca/v3/cobrancas/'.self::CODIGO => Http::response([
                'cobranca' => ['situacao' => 'A_RECEBER'],
                'boleto' => ['linhaDigitavel' => '34191', 'nossoNumero' => '17'],
                'pix' => ['pixCopiaECola' => '000201'],
            ]),
            self::BASE.'/cobranca/v3/cobrancas' => Http::response(['codigoSolicitacao' => self::CODIGO]),
        ]);

        $this->comoAdmin()
            ->post(route('cobrancas-avulsas.store', $cliente), [
                'descricao' => 'Instalação do servidor',
                'valor' => '700,00',
                'vencimento' => '2031-06-20',
            ])
            ->assertRedirect(route('clientes.show', $cliente))
            ->assertSessionHas('success', 'Cobrança avulsa emitida e o boleto foi enviado por e-mail.');

        $this->assertSame(0, Assinatura::query()->count());
        $lancamento = Lancamento::query()->sole();
        $this->assertNull($lancamento->assinatura_id);
        $this->assertSame($cliente->id, $lancamento->cliente_id);
        $this->assertSame('Instalação do servidor', $lancamento->descricao);
        $this->assertSame('700.00', $lancamento->valor);
        $this->assertSame('2031-06-20', $lancamento->vencimento->toDateString());
        $this->assertSame(self::CODIGO, $lancamento->codigo_solicitacao);
        $this->assertNotNull($lancamento->email_enviado_em);
        Mail::assertSent(CobrancaEmitida::class, function (CobrancaEmitida $mail): bool {
            return $mail->hasTo('padaria@exemplo.com')
                && $mail->assunto === 'Cobrança: Instalação do servidor';
        });
    }

    public function test_sem_dados_do_pagador_grava_o_erro_e_a_rotina_tenta_de_novo(): void
    {
        $this->travelTo('2031-06-15 15:00:00');
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()
            ->post(route('cobrancas-avulsas.store', $cliente), [
                'descricao' => 'Visita técnica',
                'valor' => '150,00',
                'vencimento' => '2031-06-20',
            ])
            ->assertRedirect(route('clientes.show', $cliente))
            ->assertSessionHas('success', 'A cobrança foi registrada, mas a emissão falhou. O motivo aparece na lista e a rotina das 08:00 tenta de novo.');

        $lancamento = Lancamento::query()->sole();
        $this->assertNull($lancamento->codigo_solicitacao);
        $this->assertStringContainsString('CPF ou CNPJ', (string) $lancamento->erro_emissao);
        Mail::assertNothingSent();

        $cliente->forceFill([
            'tipo_pessoa' => 'fisica',
            'cpf_cnpj' => '12345678901',
            'cep' => '01310100',
            'endereco' => 'Avenida Paulista',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'email' => 'padaria@exemplo.com',
        ])->save();

        Http::fake([
            self::BASE.'/oauth/v2/token' => Http::response(['access_token' => 'token-teste', 'expires_in' => 3600]),
            self::BASE.'/cobranca/v3/cobrancas/'.self::CODIGO.'/pdf' => Http::response(['pdf' => base64_encode('%PDF-1.4')]),
            self::BASE.'/cobranca/v3/cobrancas/'.self::CODIGO => Http::response([
                'cobranca' => ['situacao' => 'A_RECEBER'],
                'boleto' => ['linhaDigitavel' => '34191', 'nossoNumero' => '17'],
                'pix' => ['pixCopiaECola' => '000201'],
            ]),
            self::BASE.'/cobranca/v3/cobrancas' => Http::response(['codigoSolicitacao' => self::CODIGO]),
        ]);

        $this->artisan('cobrancas:gerar')->assertOk();

        $lancamento->refresh();
        $this->assertSame(self::CODIGO, $lancamento->codigo_solicitacao);
        $this->assertNotNull($lancamento->email_enviado_em);
    }

    public function test_vencimento_anterior_a_hoje_e_rejeitado(): void
    {
        $this->travelTo('2031-06-15 15:00:00');
        $cliente = Cliente::factory()->create();

        $this->comoAdmin()
            ->post(route('cobrancas-avulsas.store', $cliente), [
                'descricao' => 'Visita técnica',
                'valor' => '150,00',
                'vencimento' => '2031-06-14',
            ])
            ->assertSessionHasErrors(['vencimento' => 'O vencimento não pode ser anterior a hoje.']);

        $this->assertSame(0, Lancamento::query()->count());
    }

    public function test_cliente_inativo_nao_recebe_cobranca_avulsa(): void
    {
        $cliente = Cliente::factory()->create(['situacao' => SituacaoCliente::Inativo]);

        $this->comoAdmin()
            ->post(route('cobrancas-avulsas.store', $cliente), [
                'descricao' => 'Visita técnica',
                'valor' => '150,00',
                'vencimento' => '2031-06-20',
            ])
            ->assertRedirect(route('clientes.show', $cliente))
            ->assertSessionHasErrors(['error' => 'Cliente inativo não recebe cobrança nova. Reative o cliente antes.']);

        $this->assertSame(0, Lancamento::query()->count());
    }

    private function comoAdmin(): static
    {
        $admin = Usuario::query()->where('login', 'admin')->firstOrFail();

        return $this->actingAs($admin, 'painel')
            ->withSession(['permissoes' => $admin->acessos()]);
    }
}
