<?php

namespace Tests\Unit\Inter;

use App\DTO\Inter\DescontoCobrancaDTO;
use App\DTO\Inter\EditarCobrancaDTO;
use App\DTO\Inter\EmitirCobrancaDTO;
use App\DTO\Inter\FiltroCobrancasDTO;
use App\DTO\Inter\MensagemCobrancaDTO;
use App\DTO\Inter\MoraCobrancaDTO;
use App\DTO\Inter\MultaCobrancaDTO;
use App\DTO\Inter\PagadorCobrancaDTO;
use App\Enums\Inter\AmbienteInter;
use App\Enums\Inter\CodigoDesconto;
use App\Enums\Inter\CodigoMora;
use App\Enums\Inter\CodigoMulta;
use App\Enums\Inter\FormaPagamentoSandbox;
use App\Enums\Inter\FormaRecebimento;
use App\Enums\Inter\TipoPessoa;
use App\Services\Inter\AutenticacaoInter;
use App\Services\Inter\ClienteHttpInter;
use App\Services\Inter\CobrancaInterService;
use App\Services\Inter\ConfiguracaoInter;
use App\Services\Inter\ExcecaoInter;
use App\Services\Inter\WebhookCobrancaInterService;
use Illuminate\Http\Client\ConnectionException;
use PHPUnit\Framework\TestCase;

class CobrancaInterTest extends TestCase
{
    public function test_payload_de_emissao_omite_opcionais_vazios_e_normaliza_o_pagador(): void
    {
        $cobranca = new EmitirCobrancaDTO(
            seuNumero: '123456',
            valorNominal: '1500.50',
            dataVencimento: '2026-12-20',
            numDiasAgenda: 0,
            pagador: new PagadorCobrancaDTO(
                cpfCnpj: '123.456.789-01',
                tipoPessoa: TipoPessoa::Fisica,
                nome: 'Nome do pagador',
                endereco: 'Avenida Brasil, 1200',
                cidade: 'Belo Horizonte',
                uf: 'mg',
                cep: '30110-000',
            ),
            desconto: new DescontoCobrancaDTO(CodigoDesconto::PercentualDataInformada, 7, 3),
            multa: new MultaCobrancaDTO(CodigoMulta::ValorFixo, 4),
            mora: new MoraCobrancaDTO(CodigoMora::TaxaMensal, 5),
            mensagem: new MensagemCobrancaDTO(linha1: 'mensagem 1'),
        );

        $payload = $cobranca->paraPayload();

        $this->assertSame('12345678901', $payload['pagador']['cpfCnpj']);
        $this->assertSame('FISICA', $payload['pagador']['tipoPessoa']);
        $this->assertSame('MG', $payload['pagador']['uf']);
        $this->assertSame('30110000', $payload['pagador']['cep']);
        $this->assertArrayNotHasKey('email', $payload['pagador']);
        $this->assertSame(1500.5, $payload['valorNominal']);
        $this->assertSame(0, $payload['numDiasAgenda']);
        $this->assertSame([
            'codigo' => 'PERCENTUALDATAINFORMADA',
            'quantidadeDias' => 7,
            'taxa' => 3.0,
        ], $payload['desconto']);
        $this->assertSame(['codigo' => 'VALORFIXO', 'valor' => 4.0], $payload['multa']);
        $this->assertSame(['codigo' => 'TAXAMENSAL', 'taxa' => 5.0], $payload['mora']);
        $this->assertSame(['linha1' => 'mensagem 1'], $payload['mensagem']);
        $this->assertArrayNotHasKey('formasRecebimento', $payload);
    }

    public function test_mora_isenta_nao_envia_taxa(): void
    {
        $payload = (new MoraCobrancaDTO(CodigoMora::Isento))->paraPayload();

        $this->assertSame(['codigo' => 'ISENTO'], $payload);
    }

    public function test_rejeita_valor_abaixo_do_minimo_seu_numero_longo_e_somente_pix(): void
    {
        $pagador = $this->pagador();

        try {
            new EmitirCobrancaDTO('1', 2.49, '2026-12-20', 0, $pagador);
            $this->fail('O valor abaixo de 2,50 deveria ter sido recusado.');
        } catch (ExcecaoInter $excecao) {
            $this->assertStringContainsString('2,50', $excecao->getMessage());
        }

        try {
            new EmitirCobrancaDTO(str_repeat('1', 16), 10, '2026-12-20', 0, $pagador);
            $this->fail('O seu número longo deveria ter sido recusado.');
        } catch (ExcecaoInter $excecao) {
            $this->assertStringContainsString('15 caracteres', $excecao->getMessage());
        }

        try {
            new EmitirCobrancaDTO('1', 10, '2026-12-20', 0, $pagador, formasRecebimento: [FormaRecebimento::Pix]);
            $this->fail('A cobrança somente Pix deveria ter sido recusada.');
        } catch (ExcecaoInter $excecao) {
            $this->assertStringContainsString('somente Pix', $excecao->getMessage());
        }

        try {
            new EmitirCobrancaDTO('1', 10, '2026-12-20', 0, $pagador, formasRecebimento: [
                FormaRecebimento::SemFormaPagamento,
                FormaRecebimento::Boleto,
            ]);
            $this->fail('A forma sem pagamento combinada deveria ter sido recusada.');
        } catch (ExcecaoInter $excecao) {
            $this->assertStringContainsString('sem forma de pagamento', $excecao->getMessage());
        }
    }

    public function test_edicao_exige_algum_campo(): void
    {
        $this->expectException(ExcecaoInter::class);

        new EditarCobrancaDTO;
    }

    public function test_filtro_rejeita_periodo_invertido(): void
    {
        $this->expectException(ExcecaoInter::class);
        $this->expectExceptionMessage('data inicial');

        new FiltroCobrancasDTO('2026-12-31', '2026-12-01');
    }

    public function test_pagamento_simulado_so_existe_no_sandbox(): void
    {
        $servico = new CobrancaInterService(
            new ClienteHttpInter($this->configuracao(AmbienteInter::Producao), new AutenticacaoInter($this->configuracao(AmbienteInter::Producao))),
            $this->configuracao(AmbienteInter::Producao),
        );

        $this->expectException(ExcecaoInter::class);
        $this->expectExceptionMessage('sandbox');

        $servico->pagarSandbox('497f6eca-6276-4993-bfeb-53cbbbba6f08', FormaPagamentoSandbox::Pix);
    }

    public function test_cancelamento_rejeita_motivo_longo_antes_de_chamar_o_banco(): void
    {
        $configuracao = $this->configuracao();
        $servico = new CobrancaInterService(
            new ClienteHttpInter($configuracao, new AutenticacaoInter($configuracao)),
            $configuracao,
        );

        $this->expectException(ExcecaoInter::class);
        $this->expectExceptionMessage('50 caracteres');

        $servico->cancelar('497f6eca-6276-4993-bfeb-53cbbbba6f08', str_repeat('á', 51));
    }

    public function test_webhook_exige_https(): void
    {
        $configuracao = $this->configuracao();
        $servico = new WebhookCobrancaInterService(
            new ClienteHttpInter($configuracao, new AutenticacaoInter($configuracao)),
        );

        $this->expectException(ExcecaoInter::class);
        $this->expectExceptionMessage('https://');

        $servico->cadastrar('http://exemplo.com/webhook');
    }

    public function test_sandbox_explica_quando_o_certificado_e_da_producao(): void
    {
        $excecao = ExcecaoInter::conexao(
            new ConnectionException('cURL error 35: tlsv1 alert unknown ca'),
            AmbienteInter::Sandbox,
        );

        $this->assertStringContainsString('INTER_AMBIENTE=producao', $excecao->getMessage());
    }

    public function test_conta_corrente_rejeita_zero_a_esquerda_e_url_sem_https(): void
    {
        try {
            $this->configuracao(contaCorrente: '0123456');
            $this->fail('A conta com zero à esquerda deveria ter sido recusada.');
        } catch (ExcecaoInter $excecao) {
            $this->assertStringContainsString('zeros à esquerda', $excecao->getMessage());
        }

        $this->expectException(ExcecaoInter::class);
        $this->expectExceptionMessage('HTTPS');

        $this->configuracao(urlBase: 'http://cdpj-sandbox.partners.uatinter.co');
    }

    private function pagador(): PagadorCobrancaDTO
    {
        return new PagadorCobrancaDTO(
            cpfCnpj: '12345678901',
            tipoPessoa: TipoPessoa::Fisica,
            nome: 'Nome do pagador',
            endereco: 'Avenida Brasil, 1200',
            cidade: 'Belo Horizonte',
            uf: 'MG',
            cep: '30110000',
        );
    }

    private function configuracao(
        AmbienteInter $ambiente = AmbienteInter::Sandbox,
        ?string $contaCorrente = null,
        ?string $urlBase = null,
    ): ConfiguracaoInter {
        return new ConfiguracaoInter(
            ambiente: $ambiente,
            urlBase: $urlBase ?? ($ambiente === AmbienteInter::Producao
                ? 'https://cdpj.partners.bancointer.com.br'
                : 'https://cdpj-sandbox.partners.uatinter.co'),
            clientId: 'cliente-teste',
            clientSecret: 'segredo-teste',
            certificado: '/tmp/inter-certificado.crt',
            chave: '/tmp/inter-chave.key',
            senhaCertificado: null,
            contaCorrente: $contaCorrente,
            escopos: 'boleto-cobranca.read boleto-cobranca.write',
            timeout: 15,
            margemTokenSegundos: 60,
        );
    }
}
