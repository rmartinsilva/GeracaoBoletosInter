<?php

namespace Tests\Feature;

use App\DTO\Inter\EditarCobrancaDTO;
use App\DTO\Inter\EmitirCobrancaDTO;
use App\DTO\Inter\FiltroCobrancasDTO;
use App\DTO\Inter\PagadorCobrancaDTO;
use App\Enums\Inter\FormaPagamentoSandbox;
use App\Enums\Inter\TipoPessoa;
use App\Services\Inter\AutenticacaoInter;
use App\Services\Inter\CobrancaInterService;
use App\Services\Inter\ConfiguracaoInter;
use App\Services\Inter\Contracts\CobrancaInterInterface;
use App\Services\Inter\Contracts\WebhookCobrancaInterInterface;
use App\Services\Inter\ExcecaoInter;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiCobrancaInterTest extends TestCase
{
    private const SANDBOX = 'https://cdpj-sandbox.partners.uatinter.co';

    private const PRODUCAO = 'https://cdpj.partners.bancointer.com.br';

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

        Cache::flush();
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        @unlink($this->certificado);
        @unlink($this->chave);

        parent::tearDown();
    }

    public function test_configuracao_aponta_para_os_hosts_oficiais(): void
    {
        $this->assertSame(self::SANDBOX, config('inter.urls.sandbox'));
        $this->assertSame(self::PRODUCAO, config('inter.urls.producao'));
    }

    public function test_emitir_envia_mtls_conta_e_reutiliza_o_token(): void
    {
        $this->configurar(senha: 'senha-cert');
        $opcoes = null;

        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => function (Request $request, array $options) use (&$opcoes) {
                $opcoes = $options;

                return $this->token('token-teste');
            },
            self::SANDBOX.'/cobranca/v3/cobrancas' => Http::response([
                'codigoSolicitacao' => self::CODIGO,
            ]),
        ]);

        $servico = $this->app->make(CobrancaInterInterface::class);
        $this->assertInstanceOf(CobrancaInterService::class, $servico);

        $primeiro = $servico->emitir($this->emissao());
        $segundo = $servico->emitir($this->emissao('654321'));

        $this->assertSame(self::CODIGO, $primeiro['codigoSolicitacao']);
        $this->assertSame(self::CODIGO, $segundo['codigoSolicitacao']);
        $this->assertSame([$this->certificado, 'senha-cert'], $opcoes['cert']);
        $this->assertSame([$this->chave, 'senha-cert'], $opcoes['ssl_key']);
        $this->assertSame(15, $opcoes['timeout']);

        $oauth = 0;
        Http::assertSent(function (Request $request) use (&$oauth) {
            if (str_contains($request->url(), '/oauth/v2/token')) {
                $oauth++;
                $this->assertSame('client_credentials', $request->data()['grant_type']);
                $this->assertSame('cliente-teste', $request->data()['client_id']);
                $this->assertSame('segredo-teste', $request->data()['client_secret']);
                $this->assertSame('boleto-cobranca.read boleto-cobranca.write', $request->data()['scope']);

                return true;
            }

            return $request->method() === 'POST'
                && $request->url() === self::SANDBOX.'/cobranca/v3/cobrancas'
                && $request->hasHeader('Authorization', 'Bearer token-teste')
                && $request->hasHeader('x-conta-corrente', '12345678')
                && $request->data()['seuNumero'] === '123456'
                && $request->data()['pagador']['cpfCnpj'] === '12345678901';
        });
        $this->assertSame(1, $oauth);
    }

    public function test_consulta_sem_conta_nao_envia_o_cabecalho(): void
    {
        $this->configurar(conta: null);
        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => $this->token('token-teste'),
            self::SANDBOX.'/cobranca/v3/cobrancas/'.self::CODIGO => Http::response([
                'cobranca' => ['codigoSolicitacao' => self::CODIGO, 'situacao' => 'A_RECEBER'],
            ]),
        ]);

        $resposta = $this->app->make(CobrancaInterInterface::class)->consultar(self::CODIGO);

        $this->assertSame('A_RECEBER', $resposta['cobranca']['situacao']);
        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), '/cobrancas/'.self::CODIGO)
                && ! $request->hasHeader('x-conta-corrente');
        });
    }

    public function test_erro_400_expoe_as_violacoes_sem_o_segredo(): void
    {
        $this->configurar();
        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => $this->token('token-teste'),
            self::SANDBOX.'/cobranca/v3/cobrancas' => Http::response([
                'title' => 'Dados inválidos.',
                'detail' => 'Verifique os dados. segredo-teste',
                'violacoes' => [
                    ['razao' => 'Não foi possível converter o valor.', 'propriedade' => 'dataInicial'],
                ],
            ], 400),
        ]);

        try {
            $this->app->make(CobrancaInterInterface::class)->emitir($this->emissao());
            $this->fail('A resposta 400 deveria ter lançado uma exceção.');
        } catch (ExcecaoInter $excecao) {
            $this->assertSame(400, $excecao->statusHttp);
            $this->assertSame('dataInicial', $excecao->violacoes[0]['propriedade']);
            $this->assertStringContainsString('dataInicial', $excecao->getMessage());
            $this->assertStringNotContainsString('segredo-teste', $excecao->getMessage());
            $this->assertStringContainsString('[oculto]', $excecao->getMessage());
        }
    }

    public function test_falha_de_autenticacao_oculta_o_segredo(): void
    {
        $this->configurar();
        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => Http::response([
                'error_description' => 'segredo-teste rejeitado',
            ], 401),
        ]);

        try {
            $this->app->make(AutenticacaoInter::class)->autenticar();
            $this->fail('A autenticação deveria ter falhado.');
        } catch (ExcecaoInter $excecao) {
            $this->assertStringNotContainsString('segredo-teste', $excecao->getMessage());
            $this->assertStringContainsString('[oculto]', $excecao->getMessage());
        }
    }

    public function test_resposta_401_renova_o_token_e_repete_a_chamada(): void
    {
        $this->configurar(conta: null);
        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => Http::sequence()
                ->push(['access_token' => 'token-1', 'expires_in' => 3600], 200)
                ->push(['access_token' => 'token-2', 'expires_in' => 3600], 200),
            self::SANDBOX.'/cobranca/v3/cobrancas/'.self::CODIGO => Http::sequence()
                ->push(['title' => 'Não autorizado'], 401)
                ->push(['cobranca' => ['situacao' => 'A_RECEBER']], 200),
        ]);

        $resposta = $this->app->make(CobrancaInterInterface::class)->consultar(self::CODIGO);

        $this->assertSame('A_RECEBER', $resposta['cobranca']['situacao']);
        $enviadas = Http::recorded();
        $this->assertCount(4, $enviadas);
        $this->assertTrue($enviadas[3][0]->hasHeader('Authorization', 'Bearer token-2'));
    }

    public function test_token_de_sandbox_nao_vale_em_producao(): void
    {
        $this->configurar();
        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => $this->token('token-sandbox'),
            self::PRODUCAO.'/oauth/v2/token' => $this->token('token-producao'),
        ]);

        $sandbox = $this->app->make(AutenticacaoInter::class)->autenticar();
        $this->configurar('producao', conta: null);
        $producao = $this->app->make(AutenticacaoInter::class)->autenticar();

        $this->assertFalse($sandbox->reutilizado);
        $this->assertFalse($producao->reutilizado);
        $this->assertSame('token-sandbox', $sandbox->acesso);
        $this->assertSame('token-producao', $producao->acesso);
    }

    public function test_listar_editar_pdf_cancelar_e_pagar_no_sandbox(): void
    {
        $this->configurar(conta: null);
        $pdf = '%PDF-1.4';

        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => $this->token('token-teste'),
            self::SANDBOX.'/cobranca/v3/cobrancas*' => function (Request $request) use ($pdf) {
                $url = $request->url();

                if (str_ends_with(parse_url($url, PHP_URL_PATH) ?: '', '/pdf')) {
                    return Http::response(['pdf' => base64_encode($pdf)]);
                }

                if (str_contains($url, '/cancelar')) {
                    return Http::response(null, 202);
                }

                if (str_contains($url, '/pagar')) {
                    return Http::response(null, 204);
                }

                if (str_contains($url, '/sumario')) {
                    return Http::response([
                        ['situacao' => 'A_RECEBER', 'valor' => 1000, 'quantidade' => 2],
                    ]);
                }

                if ($request->method() === 'PATCH') {
                    return Http::response([
                        'status' => 'PROCESSANDO',
                        'codigoEdicao' => self::CODIGO,
                    ]);
                }

                return Http::response([
                    'totalElementos' => 1,
                    'cobrancas' => [],
                ]);
            },
        ]);

        $servico = $this->app->make(CobrancaInterInterface::class);
        $lista = $servico->listar(new FiltroCobrancasDTO('2026-11-01', '2026-11-30', itensPorPagina: 20));
        $sumario = $servico->sumario(new FiltroCobrancasDTO('2026-11-01', '2026-11-30'));
        $edicao = $servico->editar(self::CODIGO, new EditarCobrancaDTO(valorNominal: 10));
        $arquivo = $servico->recuperarPdf(self::CODIGO);
        $servico->cancelar(self::CODIGO, 'Devedor pagou por outra forma');
        $servico->pagarSandbox(self::CODIGO, FormaPagamentoSandbox::Pix);

        $this->assertSame(1, $lista['totalElementos']);
        $this->assertSame('A_RECEBER', $sumario[0]['situacao']);
        $this->assertSame('PROCESSANDO', $edicao['status']);
        $this->assertSame($pdf, $arquivo);

        Http::assertSent(function (Request $request) {
            if (! str_contains($request->url(), '/cobrancas')) {
                return false;
            }

            $caminho = parse_url($request->url(), PHP_URL_PATH) ?: '';

            if ($caminho === '/cobranca/v3/cobrancas' && $request->method() === 'GET') {
                return $request->data()['dataInicial'] === '2026-11-01'
                    && $request->data()['paginacao.itensPorPagina'] === 20
                    && $request->data()['paginacao.paginaAtual'] === 0;
            }

            if (str_ends_with($caminho, '/sumario')) {
                return ! array_key_exists('paginacao.itensPorPagina', $request->data());
            }

            if ($request->method() === 'PATCH') {
                return $request->data()['valorNominal'] === 10.0;
            }

            if (str_ends_with($caminho, '/cancelar')) {
                return $request->data()['motivoCancelamento'] === 'Devedor pagou por outra forma';
            }

            if (str_ends_with($caminho, '/pagar')) {
                return str_starts_with($request->url(), self::SANDBOX)
                    && $request->data()['pagarCom'] === 'PIX';
            }

            return true;
        });
    }

    public function test_pdf_invalido_e_emissao_sem_codigo_falham(): void
    {
        $this->configurar(conta: null);
        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => $this->token('token-teste'),
            self::SANDBOX.'/cobranca/v3/cobrancas/'.self::CODIGO.'/pdf' => Http::response(['pdf' => '***']),
        ]);

        try {
            $this->app->make(CobrancaInterInterface::class)->recuperarPdf(self::CODIGO);
            $this->fail('O base64 inválido deveria ter sido recusado.');
        } catch (ExcecaoInter $excecao) {
            $this->assertStringContainsString('base64', $excecao->getMessage());
        }

        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => $this->token('token-teste'),
            self::SANDBOX.'/cobranca/v3/cobrancas' => Http::response(['ok' => true]),
        ]);

        $this->expectException(ExcecaoInter::class);
        $this->expectExceptionMessage('codigoSolicitacao');

        $this->app->make(CobrancaInterInterface::class)->emitir($this->emissao());
    }

    public function test_webhook_e_cadastrado_por_https(): void
    {
        $this->configurar();
        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => $this->token('token-teste'),
            self::SANDBOX.'/cobranca/v3/cobrancas/webhook' => Http::response(null, 204),
        ]);

        $this->app->make(WebhookCobrancaInterInterface::class)
            ->cadastrar('https://cobranca.example.com/api/webhook');

        Http::assertSent(function (Request $request) {
            return $request->method() === 'PUT'
                && $request->url() === self::SANDBOX.'/cobranca/v3/cobrancas/webhook'
                && $request->data()['webhookUrl'] === 'https://cobranca.example.com/api/webhook'
                && $request->hasHeader('x-conta-corrente', '12345678');
        });
    }

    public function test_comando_verificar_autentica_reutiliza_o_cache_e_nao_exibe_segredo(): void
    {
        $this->configurar();
        Http::fake([
            self::SANDBOX.'/oauth/v2/token' => $this->token('token-super-secreto'),
        ]);

        $this->artisan('inter:verificar')
            ->expectsOutputToContain('Sandbox')
            ->expectsOutputToContain('Autenticação concluída. O token foi armazenado em cache.')
            ->expectsOutputToContain('INTER_AMBIENTE')
            ->doesntExpectOutputToContain('token-super-secreto')
            ->doesntExpectOutputToContain('segredo-teste')
            ->assertSuccessful();

        $this->artisan('inter:verificar')
            ->expectsOutputToContain('já estava em cache')
            ->assertSuccessful();

        Http::assertSentCount(1);

        $this->artisan('inter:verificar', ['--renovar' => true])->assertSuccessful();

        Http::assertSentCount(2);
    }

    public function test_comando_verificar_explica_configuracao_incompleta(): void
    {
        config([
            'inter.ambiente' => 'sandbox',
            'inter.url_base' => self::SANDBOX,
            'inter.client_id' => '',
            'inter.client_secret' => 'segredo-teste',
            'inter.certificado' => $this->certificado,
            'inter.chave' => $this->chave,
            'inter.escopos' => 'boleto-cobranca.read boleto-cobranca.write',
            'inter.timeout' => 15,
            'inter.margem_token_segundos' => 60,
            'inter.conta_corrente' => null,
            'inter.certificado_senha' => null,
        ]);
        $this->app->forgetInstance(ConfiguracaoInter::class);

        $this->artisan('inter:verificar')
            ->expectsOutputToContain('INTER_CLIENT_ID')
            ->assertFailed();
    }

    public function test_ambiente_invalido_e_certificado_ausente_falham_antes_da_rede(): void
    {
        config([
            'inter.ambiente' => 'homologacao',
            'inter.url_base' => null,
        ]);
        $this->app->forgetInstance(ConfiguracaoInter::class);

        try {
            $this->app->make(ConfiguracaoInter::class);
            $this->fail('O ambiente inválido deveria ter sido recusado.');
        } catch (ExcecaoInter $excecao) {
            $this->assertStringContainsString('INTER_AMBIENTE', $excecao->getMessage());
        }

        config([
            'inter.ambiente' => 'sandbox',
            'inter.url_base' => self::SANDBOX,
            'inter.client_id' => 'cliente-teste',
            'inter.client_secret' => 'segredo-teste',
            'inter.certificado' => '/tmp/inter-inexistente-'.uniqid().'.crt',
            'inter.chave' => $this->chave,
            'inter.escopos' => 'boleto-cobranca.read boleto-cobranca.write',
            'inter.timeout' => 15,
            'inter.margem_token_segundos' => 60,
            'inter.conta_corrente' => null,
            'inter.certificado_senha' => null,
        ]);
        $this->app->forgetInstance(ConfiguracaoInter::class);

        $this->expectException(ExcecaoInter::class);
        $this->expectExceptionMessage('certificado');

        $this->app->make(ConfiguracaoInter::class);
    }

    private function configurar(string $ambiente = 'sandbox', ?string $conta = '12345678', ?string $senha = null): void
    {
        config([
            'inter.ambiente' => $ambiente,
            'inter.url_base' => $ambiente === 'producao' ? self::PRODUCAO : self::SANDBOX,
            'inter.client_id' => 'cliente-teste',
            'inter.client_secret' => 'segredo-teste',
            'inter.certificado' => $this->certificado,
            'inter.chave' => $this->chave,
            'inter.certificado_senha' => $senha,
            'inter.conta_corrente' => $conta,
            'inter.escopos' => 'boleto-cobranca.read boleto-cobranca.write',
            'inter.timeout' => 15,
            'inter.margem_token_segundos' => 60,
        ]);

        $this->app->forgetInstance(ConfiguracaoInter::class);
    }

    private function emissao(string $seuNumero = '123456'): EmitirCobrancaDTO
    {
        return new EmitirCobrancaDTO(
            seuNumero: $seuNumero,
            valorNominal: 1500.5,
            dataVencimento: '2026-12-20',
            numDiasAgenda: 30,
            pagador: new PagadorCobrancaDTO(
                cpfCnpj: '123.456.789-01',
                tipoPessoa: TipoPessoa::Fisica,
                nome: 'Nome do pagador',
                endereco: 'Avenida Brasil, 1200',
                cidade: 'Belo Horizonte',
                uf: 'mg',
                cep: '30110-000',
            ),
        );
    }

    private function token(string $acesso): PromiseInterface
    {
        return Http::response([
            'access_token' => $acesso,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'boleto-cobranca.read boleto-cobranca.write',
        ]);
    }
}
