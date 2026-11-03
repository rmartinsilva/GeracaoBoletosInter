<?php

namespace Tests\Feature;

use App\Enums\TipoPessoa;
use App\Models\Cliente;
use App\Services\Inter\ConfiguracaoInter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImportarClientesTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://cdpj-sandbox.partners.uatinter.co';

    private const PADARIA = '11111111-1111-4111-8111-111111111111';

    private const MERCADO = '22222222-2222-4222-8222-222222222222';

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
    }

    protected function tearDown(): void
    {
        @unlink($this->certificado);
        @unlink($this->chave);

        parent::tearDown();
    }

    public function test_importa_pagadores_novos_e_nao_duplica_na_segunda_execucao(): void
    {
        Cliente::factory()->create([
            'nome' => 'Cliente Antigo',
            'cpf_cnpj' => '12345678901',
        ]);

        Http::fake([
            self::BASE.'/oauth/v2/token' => Http::response([
                'access_token' => 'token-teste',
                'expires_in' => 3600,
            ]),
            self::BASE.'/cobranca/v3/cobrancas*' => function (Request $request) {
                $caminho = parse_url($request->url(), PHP_URL_PATH);

                if ($caminho === '/cobranca/v3/cobrancas/'.self::PADARIA) {
                    return Http::response($this->detalhe(
                        self::PADARIA,
                        '12345678000199',
                        'JURIDICA',
                        'Padaria Pão Quente',
                        'financeiro@paoquente.com.br',
                    ));
                }

                if ($caminho === '/cobranca/v3/cobrancas/'.self::MERCADO) {
                    return Http::response($this->detalhe(
                        self::MERCADO,
                        '98765432100',
                        'FISICA',
                        'Mercado da Esquina',
                        '',
                    ));
                }

                $pagina = (int) $request->data()['paginacao.paginaAtual'];

                if ($pagina === 0) {
                    return Http::response([
                        'ultimaPagina' => false,
                        'cobrancas' => [
                            $this->item(self::PADARIA, '12345678000199', 'Padaria Pão Quente'),
                            $this->item('33333333-3333-4333-8333-333333333333', '12345678901', 'Cliente Antigo'),
                            $this->item('44444444-4444-4444-8444-444444444444', 'sem-documento', 'Sem documento'),
                        ],
                    ]);
                }

                return Http::response([
                    'ultimaPagina' => true,
                    'cobrancas' => [
                        $this->item(self::MERCADO, '987.654.321-00', 'Mercado da Esquina'),
                        $this->item(self::PADARIA, '12.345.678/0001-99', 'Padaria Pão Quente'),
                    ],
                ]);
            },
        ]);

        $this->artisan('clientes:importar', [
            '--de' => '2021-01-01',
            '--ate' => '2026-11-03',
            '--por' => 'emissao',
        ])->assertOk()
            ->expectsOutput('Importados: 2')
            ->expectsOutput('Sem e-mail: 1')
            ->expectsOutput('Já cadastrados: 1')
            ->expectsOutput('Ignorados: 0')
            ->expectsOutput('Falhas: 0');

        $this->assertSame(3, Cliente::query()->count());

        $padaria = Cliente::query()->where('cpf_cnpj', '12345678000199')->first();
        $this->assertNotNull($padaria);
        $this->assertSame('Padaria Pão Quente', $padaria->nome);
        $this->assertSame('financeiro@paoquente.com.br', $padaria->email);
        $this->assertSame(TipoPessoa::Juridica, $padaria->tipo_pessoa);
        $this->assertSame('Avenida Brasil', $padaria->endereco);
        $this->assertSame('30110000', $padaria->cep);
        $this->assertSame('MG', $padaria->uf);

        $mercado = Cliente::query()->where('cpf_cnpj', '98765432100')->first();
        $this->assertNotNull($mercado);
        $this->assertNull($mercado->email);
        $this->assertSame(TipoPessoa::Fisica, $mercado->tipo_pessoa);

        $this->artisan('clientes:importar', [
            '--de' => '2021-01-01',
            '--ate' => '2026-11-03',
        ])->assertOk()
            ->expectsOutput('Importados: 0')
            ->expectsOutput('Já cadastrados: 3');

        $this->assertSame(3, Cliente::query()->count());
    }

    public function test_filtro_de_periodo_invalido_nao_consulta_o_banco(): void
    {
        Http::fake();

        $this->artisan('clientes:importar', ['--por' => 'competencia'])
            ->assertFailed();

        Http::assertNothingSent();
    }

    /**
     * @return array<string, mixed>
     */
    private function item(string $codigo, string $documento, string $nome): array
    {
        return [
            'cobranca' => [
                'codigoSolicitacao' => $codigo,
                'pagador' => [
                    'nome' => $nome,
                    'cpfCnpj' => $documento,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detalhe(string $codigo, string $documento, string $tipo, string $nome, string $email): array
    {
        return [
            'cobranca' => [
                'codigoSolicitacao' => $codigo,
                'pagador' => [
                    'email' => $email,
                    'numero' => '1200',
                    'complemento' => 'loja 1',
                    'cpfCnpj' => $documento,
                    'tipoPessoa' => $tipo,
                    'nome' => $nome,
                    'endereco' => 'Avenida Brasil',
                    'bairro' => 'Centro',
                    'cidade' => 'Belo Horizonte',
                    'uf' => 'mg',
                    'cep' => '30110-000',
                ],
            ],
        ];
    }
}
