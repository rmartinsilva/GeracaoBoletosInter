<?php

namespace App\Services;

use App\DTO\Clientes\CreateClienteDTO;
use App\DTO\Inter\FiltroCobrancasDTO;
use App\Enums\Inter\FiltrarDataPor;
use App\Enums\Inter\OrdenarCobrancasPor;
use App\Enums\Inter\TipoOrdenacao;
use App\Enums\TipoPessoa;
use App\Repositories\Contracts\ClienteRepositoryInterface;
use App\Services\Inter\Contracts\CobrancaInterInterface;
use App\Services\Inter\ExcecaoInter;

/**
 * Lê as cobranças do ambiente configurado e cadastra cada pagador uma única vez.
 * A listagem só traz nome e documento; o endereço vem da consulta da cobrança mais recente.
 */
class ImportadorDeClientes
{
    private const ITENS_POR_PAGINA = 1000;

    private const LIMITE_DE_PAGINAS = 500;

    public function __construct(
        private CobrancaInterInterface $cobrancas,
        private ClienteRepositoryInterface $clientes,
    ) {}

    /**
     * @return array{importados: int, sem_email: int, existentes: int, ignorados: int, falhas: int}
     */
    public function executar(string $dataInicial, string $dataFinal, FiltrarDataPor $por): array
    {
        $resumo = [
            'importados' => 0,
            'sem_email' => 0,
            'existentes' => 0,
            'ignorados' => 0,
            'falhas' => 0,
        ];

        foreach ($this->codigosPorDocumento($dataInicial, $dataFinal, $por) as $documento => $codigo) {
            if ($this->clientes->buscarPorCpfCnpj($documento) !== null) {
                $resumo['existentes']++;

                continue;
            }

            try {
                $detalhe = $this->cobrancas->consultar($codigo);
            } catch (ExcecaoInter) {
                $resumo['falhas']++;

                continue;
            }

            $dto = $this->montarCliente($detalhe, $documento);

            if ($dto === null) {
                $resumo['ignorados']++;

                continue;
            }

            $this->clientes->criar($dto);
            $resumo['importados']++;

            if ($dto->email === null) {
                $resumo['sem_email']++;
            }
        }

        return $resumo;
    }

    /**
     * @return array<string, string>
     */
    private function codigosPorDocumento(string $dataInicial, string $dataFinal, FiltrarDataPor $por): array
    {
        $codigos = [];
        $pagina = 0;

        do {
            $resposta = $this->cobrancas->listar(new FiltroCobrancasDTO(
                dataInicial: $dataInicial,
                dataFinal: $dataFinal,
                filtrarDataPor: $por,
                itensPorPagina: self::ITENS_POR_PAGINA,
                paginaAtual: $pagina,
                ordenarPor: OrdenarCobrancasPor::DataEmissao,
                tipoOrdenacao: TipoOrdenacao::Desc,
            ));

            $itens = $resposta['cobrancas'] ?? [];

            if (! is_array($itens) || $itens === []) {
                break;
            }

            foreach ($itens as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $cobranca = $item['cobranca'] ?? null;

                if (! is_array($cobranca)) {
                    continue;
                }

                $documento = $this->documento($cobranca['pagador']['cpfCnpj'] ?? null);
                $codigo = $cobranca['codigoSolicitacao'] ?? null;

                if ($documento === null || ! is_string($codigo) || $codigo === '' || isset($codigos[$documento])) {
                    continue;
                }

                $codigos[$documento] = $codigo;
            }

            $ultima = array_key_exists('ultimaPagina', $resposta)
                ? (bool) $resposta['ultimaPagina']
                : count($itens) < self::ITENS_POR_PAGINA;
            $pagina++;
        } while (! $ultima && $pagina < self::LIMITE_DE_PAGINAS);

        return $codigos;
    }

    /**
     * @param  array<mixed>  $detalhe
     */
    private function montarCliente(array $detalhe, string $documento): ?CreateClienteDTO
    {
        $pagador = $detalhe['cobranca']['pagador'] ?? null;

        if (! is_array($pagador)) {
            return null;
        }

        $nome = $this->texto($pagador['nome'] ?? null, 255);

        if ($nome === null) {
            return null;
        }

        $documentoDetalhe = $this->documento($pagador['cpfCnpj'] ?? null) ?? $documento;

        return new CreateClienteDTO(
            nome: $nome,
            email: $this->email($pagador['email'] ?? null),
            nome_contato: $nome,
            tipo_pessoa: $this->tipoPessoa($pagador['tipoPessoa'] ?? null, $documentoDetalhe),
            cpf_cnpj: $documentoDetalhe,
            cep: $this->cep($pagador['cep'] ?? null),
            endereco: $this->texto($pagador['endereco'] ?? null, 255),
            numero: $this->texto($pagador['numero'] ?? null, 20),
            complemento: $this->texto($pagador['complemento'] ?? null, 100),
            bairro: $this->texto($pagador['bairro'] ?? null, 100),
            cidade: $this->texto($pagador['cidade'] ?? null, 100),
            uf: $this->uf($pagador['uf'] ?? null),
        );
    }

    private function documento(mixed $valor): ?string
    {
        if (! is_scalar($valor)) {
            return null;
        }

        $digitos = preg_replace('/\D/', '', (string) $valor) ?? '';

        if (! in_array(strlen($digitos), [11, 14], true)) {
            return null;
        }

        return $digitos;
    }

    private function texto(mixed $valor, int $limite): ?string
    {
        if (! is_scalar($valor)) {
            return null;
        }

        $texto = trim((string) $valor);

        if ($texto === '') {
            return null;
        }

        return mb_substr($texto, 0, $limite);
    }

    private function email(mixed $valor): ?string
    {
        $email = $this->texto($valor, 255);

        if ($email === null || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        return $email;
    }

    private function cep(mixed $valor): ?string
    {
        if (! is_scalar($valor)) {
            return null;
        }

        $digitos = preg_replace('/\D/', '', (string) $valor) ?? '';

        return strlen($digitos) === 8 ? $digitos : null;
    }

    private function uf(mixed $valor): ?string
    {
        $uf = $this->texto($valor, 2);

        if ($uf === null || preg_match('/^[A-Za-z]{2}$/', $uf) !== 1) {
            return null;
        }

        return strtoupper($uf);
    }

    private function tipoPessoa(mixed $valor, string $documento): TipoPessoa
    {
        $texto = is_scalar($valor) ? strtoupper(trim((string) $valor)) : '';

        return match ($texto) {
            'FISICA' => TipoPessoa::Fisica,
            'JURIDICA' => TipoPessoa::Juridica,
            default => strlen($documento) === 14 ? TipoPessoa::Juridica : TipoPessoa::Fisica,
        };
    }
}
