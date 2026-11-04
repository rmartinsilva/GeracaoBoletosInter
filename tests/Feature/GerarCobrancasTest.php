<?php

namespace Tests\Feature;

use App\Mail\CobrancaEmitida;
use App\Mail\FalhaCobranca;
use App\Models\Assinatura;
use App\Models\Cliente;
use App\Models\ConfiguracaoCobranca;
use App\Models\Lancamento;
use App\Services\Inter\ConfiguracaoInter;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GerarCobrancasTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://cdpj-sandbox.partners.uatinter.co';

    private const CODIGO = '497f6eca-6276-4993-bfeb-53cbbbba6f08';

    private string $certificado;

    private string $chave;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_no_dia_de_geracao_emite_o_boleto_e_envia_o_email(): void
    {
        $this->travelTo('2031-06-05 15:00:00');
        $cliente = Cliente::factory()->comPagador()->create(['nome' => 'Padaria Sol']);
        Assinatura::factory()->create([
            'cliente_id' => $cliente->id,
            'descricao' => 'Mensalidade',
            'valor' => 150,
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
            'data_inicio' => '2031-01-10',
        ]);

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

        $this->artisan('cobrancas:gerar')
            ->expectsOutputToContain('Emitidas: 1')
            ->expectsOutputToContain('E-mails enviados: 1')
            ->assertOk();

        $lancamento = Lancamento::query()->sole();
        $this->assertSame(self::CODIGO, $lancamento->codigo_solicitacao);
        $this->assertSame('34191', $lancamento->linha_digitavel);
        $this->assertSame('000201', $lancamento->pix_copia_cola);
        $this->assertNotNull($lancamento->email_enviado_em);
        $this->assertNull($lancamento->erro_emissao);
        Storage::disk('local')->assertExists('boletos/'.$lancamento->id.'.pdf');

        Http::assertSent(function (Request $request) use ($lancamento): bool {
            if ($request->method() !== 'POST' || ! str_ends_with($request->url(), '/cobranca/v3/cobrancas')) {
                return false;
            }

            return $request->data()['seuNumero'] === 'L'.str_pad((string) $lancamento->id, 14, '0', STR_PAD_LEFT)
                && $request->data()['dataVencimento'] === '2031-06-10'
                && $request->data()['numDiasAgenda'] === 30
                && $request->data()['pagador']['cpfCnpj'] === '12345678901';
        });

        Mail::assertSent(CobrancaEmitida::class, function (CobrancaEmitida $mail) use ($cliente): bool {
            return $mail->hasTo($cliente->email)
                && $mail->assunto === 'Cobrança: Mensalidade'
                && str_contains($mail->texto, 'Padaria Sol')
                && str_contains($mail->texto, 'R$ 150,00')
                && str_contains($mail->texto, '10/06/2031')
                && str_contains($mail->texto, '34191')
                && str_contains($mail->texto, '000201');
        });

        $this->artisan('cobrancas:gerar')->assertOk();
        Mail::assertSentCount(1);
        $this->assertSame(1, $this->postsDeEmissao());
    }

    public function test_envia_para_todos_os_emails_do_cliente_e_a_copia_oculta(): void
    {
        $this->travelTo('2031-06-05 15:00:00');
        ConfiguracaoCobranca::atual()->update(['email_copia' => 'conferencia@empresa.com.br']);
        $cliente = Cliente::factory()->comPagador()->create([
            'email' => 'financeiro@paoquente.com.br',
            'emails_adicionais' => "contato@paoquente.com.br\noutro@paoquente.com.br",
        ]);
        Assinatura::factory()->create([
            'cliente_id' => $cliente->id,
            'descricao' => 'Mensalidade',
            'valor' => 150,
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
            'data_inicio' => '2031-01-10',
        ]);
        $this->fingirBoletoPronto();

        $this->artisan('cobrancas:gerar')->assertOk();

        Mail::assertSent(CobrancaEmitida::class, function (CobrancaEmitida $mail): bool {
            return $mail->hasTo('financeiro@paoquente.com.br')
                && $mail->hasTo('contato@paoquente.com.br')
                && $mail->hasTo('outro@paoquente.com.br')
                && $mail->hasBcc('conferencia@empresa.com.br');
        });
    }

    public function test_nao_repete_a_copia_oculta_quando_ela_ja_e_destinataria(): void
    {
        $this->travelTo('2031-06-05 15:00:00');
        ConfiguracaoCobranca::atual()->update(['email_copia' => 'financeiro@paoquente.com.br']);
        $cliente = Cliente::factory()->comPagador()->create([
            'email' => 'financeiro@paoquente.com.br',
        ]);
        Assinatura::factory()->create([
            'cliente_id' => $cliente->id,
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
            'data_inicio' => '2031-01-10',
        ]);
        $this->fingirBoletoPronto();

        $this->artisan('cobrancas:gerar')->assertOk();

        Mail::assertSent(CobrancaEmitida::class, function (CobrancaEmitida $mail): bool {
            return $mail->hasTo('financeiro@paoquente.com.br')
                && ! $mail->hasBcc('financeiro@paoquente.com.br');
        });
    }

    public function test_sem_dados_do_pagador_grava_o_erro_e_nao_chama_o_banco(): void
    {
        $this->travelTo('2031-06-05 15:00:00');
        Assinatura::factory()->create([
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
            'data_inicio' => '2031-01-10',
            'valor' => 150,
        ]);

        $this->artisan('cobrancas:gerar')
            ->expectsOutputToContain('Falhas: 1')
            ->assertOk();

        $lancamento = Lancamento::query()->sole();
        $this->assertNull($lancamento->codigo_solicitacao);
        $this->assertStringContainsString('CPF ou CNPJ', (string) $lancamento->erro_emissao);
        Mail::assertNothingSent();
        $this->assertSame(0, $this->postsDeEmissao());
    }

    public function test_falha_avisa_o_email_configurado_uma_unica_vez(): void
    {
        $this->travelTo('2031-06-05 15:00:00');
        ConfiguracaoCobranca::atual()->update(['email_falha' => 'operador@exemplo.com']);
        $cliente = Cliente::factory()->create(['nome' => 'Padaria Sol']);
        Assinatura::factory()->create([
            'cliente_id' => $cliente->id,
            'descricao' => 'Servidor Mensal',
            'dia_vencimento' => 10,
            'dia_geracao' => 5,
            'data_inicio' => '2031-01-10',
            'valor' => 700,
        ]);

        $this->artisan('cobrancas:gerar')->assertOk();

        $lancamento = Lancamento::query()->sole();
        $this->assertNotNull($lancamento->falha_notificada_em);
        Mail::assertSent(FalhaCobranca::class, function (FalhaCobranca $mail): bool {
            return $mail->hasTo('operador@exemplo.com')
                && $mail->assunto === 'Falha na cobrança: Servidor Mensal'
                && str_contains($mail->texto, 'Padaria Sol')
                && str_contains($mail->texto, 'R$ 700,00')
                && str_contains($mail->texto, 'CPF ou CNPJ');
        });

        $this->artisan('cobrancas:gerar')->assertOk();
        Mail::assertSentCount(1);
    }

    public function test_cobranca_ja_emitida_recebe_o_email_quando_o_boleto_fica_pronto(): void
    {
        $this->travelTo('2031-06-06 15:00:00');
        $cliente = Cliente::factory()->comPagador()->create();
        $assinatura = Assinatura::factory()->create([
            'cliente_id' => $cliente->id,
            'dia_geracao' => 5,
            'dia_vencimento' => 10,
            'descricao' => 'Mensalidade',
            'valor' => 150,
        ]);
        Lancamento::factory()->create([
            'assinatura_id' => $assinatura->id,
            'competencia' => '2031-06-01',
            'vencimento' => '2031-06-10',
            'valor' => 150,
            'codigo_solicitacao' => self::CODIGO,
        ]);

        Http::fake([
            self::BASE.'/oauth/v2/token' => Http::response(['access_token' => 'token-teste', 'expires_in' => 3600]),
            self::BASE.'/cobranca/v3/cobrancas/'.self::CODIGO.'/pdf' => Http::response(['pdf' => base64_encode('%PDF-1.4')]),
            self::BASE.'/cobranca/v3/cobrancas/'.self::CODIGO => Http::response([
                'cobranca' => ['situacao' => 'A_RECEBER'],
                'boleto' => ['linhaDigitavel' => '34191', 'nossoNumero' => '17'],
                'pix' => ['pixCopiaECola' => '000201'],
            ]),
        ]);

        $this->artisan('cobrancas:gerar')
            ->expectsOutputToContain('Emitidas: 0')
            ->expectsOutputToContain('E-mails enviados: 1')
            ->assertOk();

        Mail::assertSent(CobrancaEmitida::class);
        $this->assertSame(0, $this->postsDeEmissao());
    }

    public function test_comando_roda_todo_dia_as_oito_em_brasilia(): void
    {
        $evento = collect(app(Schedule::class)->events())
            ->first(fn ($evento) => str_contains((string) $evento->command, 'cobrancas:gerar'));

        $this->assertNotNull($evento);
        $this->assertSame('0 8 * * *', $evento->expression);
        $this->assertSame('America/Sao_Paulo', $evento->timezone);
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
}
