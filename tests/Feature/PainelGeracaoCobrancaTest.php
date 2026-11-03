<?php

namespace Tests\Feature;

use App\Mail\CobrancaEmitida;
use App\Models\Acesso;
use App\Models\Assinatura;
use App\Models\Cliente;
use App\Models\Grupo;
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

class PainelGeracaoCobrancaTest extends TestCase
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

    public function test_dashboard_oferece_o_botao_para_quem_edita_clientes(): void
    {
        $this->comoAdmin()->get('/painel')
            ->assertOk()
            ->assertSee('Gerar cobranças agora')
            ->assertSee(route('cobrancas.gerar'), false)
            ->assertSee('todo dia às 08:00', false);
    }

    public function test_quem_so_visualiza_nao_ve_o_botao(): void
    {
        $this->comoUsuarioCom(['cliente_visualizar'])->get('/painel')
            ->assertOk()
            ->assertDontSee('Gerar cobranças agora');
    }

    public function test_quem_so_visualiza_nao_dispara_a_rotina(): void
    {
        $this->comoUsuarioCom(['cliente_visualizar'])
            ->post(route('cobrancas.gerar'))
            ->assertForbidden();
    }

    public function test_botao_emite_a_cobranca_do_dia(): void
    {
        $this->travelTo('2031-06-05 15:00:00');
        $cliente = Cliente::factory()->comPagador()->create();
        Assinatura::factory()->create([
            'cliente_id' => $cliente->id,
            'descricao' => 'Mensalidade',
            'valor' => 150,
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
            'data_inicio' => '2031-01-10',
        ]);
        $this->fingirBoletoPronto();

        $this->comoAdmin()->post(route('cobrancas.gerar'))
            ->assertRedirect(route('painel.dashboard'))
            ->assertSessionHas('success', 'Rotina concluída. Emitidas: 1. E-mails enviados: 1. Aguardando o boleto: 0. Falhas: 0.');

        $lancamento = Lancamento::query()->sole();
        $this->assertSame(self::CODIGO, $lancamento->codigo_solicitacao);
        $this->assertNotNull($lancamento->email_enviado_em);
        Mail::assertSent(CobrancaEmitida::class);
        $this->assertSame(1, $this->postsDeEmissao());
    }

    public function test_botao_retoma_cobranca_que_falhou_fora_do_dia_de_geracao(): void
    {
        $this->travelTo('2031-06-06 15:00:00');
        $cliente = Cliente::factory()->comPagador()->create();
        $assinatura = Assinatura::factory()->create([
            'cliente_id' => $cliente->id,
            'descricao' => 'Mensalidade',
            'valor' => 150,
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
            'data_inicio' => '2031-01-10',
        ]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-06-01',
            'vencimento' => '2031-06-10',
            'valor' => 150,
            'erro_emissao' => 'Não foi possível conectar à API do Banco Inter.',
        ]);
        $this->fingirBoletoPronto();

        $this->comoAdmin()->post(route('cobrancas.gerar'))
            ->assertRedirect(route('painel.dashboard'))
            ->assertSessionHas('success', 'Rotina concluída. Emitidas: 1. E-mails enviados: 1. Aguardando o boleto: 0. Falhas: 0.');

        $lancamento = Lancamento::query()->sole();
        $this->assertSame(self::CODIGO, $lancamento->codigo_solicitacao);
        $this->assertNull($lancamento->erro_emissao);
        Mail::assertSent(CobrancaEmitida::class);
    }

    public function test_botao_nao_emite_lancamento_ainda_sem_tentativa(): void
    {
        $this->travelTo('2031-06-06 15:00:00');
        $assinatura = Assinatura::factory()->create([
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
            'data_inicio' => '2031-01-10',
        ]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-06-01',
            'vencimento' => '2031-06-10',
        ]);

        $this->comoAdmin()->post(route('cobrancas.gerar'))
            ->assertRedirect(route('painel.dashboard'))
            ->assertSessionHas('success', 'Rotina concluída. Emitidas: 0. E-mails enviados: 0. Aguardando o boleto: 0. Falhas: 0.');

        $this->assertNull(Lancamento::query()->sole()->codigo_solicitacao);
        Http::assertNothingSent();
    }

    private function fingirBoletoPronto(): void
    {
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
    }

    private function postsDeEmissao(): int
    {
        return collect(Http::recorded())
            ->filter(fn (array $par) => $par[0]->method() === 'POST' && str_ends_with($par[0]->url(), '/cobranca/v3/cobrancas'))
            ->count();
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
        $usuario = Usuario::query()->create([
            'login' => 'operador',
            'name' => 'Operador',
            'password' => 'senha123',
        ]);

        $grupo = Grupo::query()->create(['descricao' => 'Operação']);
        $grupo->acessos()->sync(Acesso::query()->whereIn('key', $permissoes)->pluck('id'));
        $usuario->grupos()->sync([$grupo->id]);

        return $this->actingAs($usuario, 'painel')
            ->withSession(['permissoes' => $usuario->fresh()->acessos()]);
    }
}
