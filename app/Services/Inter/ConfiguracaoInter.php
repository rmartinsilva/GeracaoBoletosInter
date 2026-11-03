<?php

namespace App\Services\Inter;

use App\Enums\Inter\AmbienteInter;

class ConfiguracaoInter
{
    public const CAMINHO_COBRANCAS = '/cobranca/v3/cobrancas';

    public function __construct(
        public readonly AmbienteInter $ambiente,
        public readonly string $urlBase,
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $certificado,
        public readonly string $chave,
        public readonly ?string $senhaCertificado,
        public readonly ?string $contaCorrente,
        public readonly string $escopos,
        public readonly int $timeout,
        public readonly int $margemTokenSegundos,
    ) {
        if (! str_starts_with($this->urlBase, 'https://')) {
            throw new ExcecaoInter('A URL base da API do Banco Inter deve usar HTTPS.');
        }

        if ($this->clientId === '' || $this->clientSecret === '') {
            throw new ExcecaoInter('Informe o cliente e o segredo da aplicação do Banco Inter.');
        }

        if ($this->contaCorrente !== null && ! preg_match('/^[1-9][0-9]*$/', $this->contaCorrente)) {
            throw new ExcecaoInter('A conta corrente do Banco Inter deve conter apenas números, incluindo o dígito, sem zeros à esquerda.');
        }

        if ($this->timeout < 1) {
            throw new ExcecaoInter('O tempo limite da API do Banco Inter deve ser de pelo menos 1 segundo.');
        }

        if ($this->margemTokenSegundos < 0) {
            throw new ExcecaoInter('A margem de renovação do token do Banco Inter não pode ser negativa.');
        }
    }

    public static function fromConfig(): self
    {
        $ambiente = AmbienteInter::tryFrom((string) config('inter.ambiente'));

        if ($ambiente === null) {
            throw new ExcecaoInter('O ambiente do Banco Inter deve ser "sandbox" ou "producao" (INTER_AMBIENTE).');
        }

        $urlBase = config('inter.url_base');

        if (! is_string($urlBase) || $urlBase === '') {
            throw new ExcecaoInter('A URL base da API do Banco Inter não está configurada para o ambiente "'.config('inter.ambiente').'".');
        }

        $conta = config('inter.conta_corrente');
        $conta = $conta === null || $conta === '' ? null : trim((string) $conta);

        $senha = config('inter.certificado_senha');
        $senha = is_string($senha) && $senha !== '' ? $senha : null;

        return new self(
            ambiente: $ambiente,
            urlBase: rtrim($urlBase, '/'),
            clientId: self::obrigatorio(config('inter.client_id'), 'INTER_CLIENT_ID', 'Client ID'),
            clientSecret: self::obrigatorio(config('inter.client_secret'), 'INTER_CLIENT_SECRET', 'Client Secret'),
            certificado: self::arquivo(config('inter.certificado'), 'INTER_CERTIFICADO', 'certificado'),
            chave: self::arquivo(config('inter.chave'), 'INTER_CHAVE', 'chave'),
            senhaCertificado: $senha,
            contaCorrente: $conta,
            escopos: self::obrigatorio(config('inter.escopos'), 'INTER_ESCOPOS', 'escopo'),
            timeout: (int) config('inter.timeout'),
            margemTokenSegundos: (int) config('inter.margem_token_segundos'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function opcoesTls(): array
    {
        return [
            'cert' => $this->senhaCertificado === null
                ? $this->certificado
                : [$this->certificado, $this->senhaCertificado],
            'ssl_key' => $this->senhaCertificado === null
                ? $this->chave
                : [$this->chave, $this->senhaCertificado],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function cabecalhos(): array
    {
        if ($this->contaCorrente === null) {
            return [];
        }

        return ['x-conta-corrente' => $this->contaCorrente];
    }

    private static function obrigatorio(mixed $valor, string $variavel, string $rotulo): string
    {
        if (! is_string($valor) || trim($valor) === '') {
            throw new ExcecaoInter("Informe o {$rotulo} do Banco Inter em {$variavel}.");
        }

        return trim($valor);
    }

    private static function arquivo(mixed $caminho, string $variavel, string $rotulo): string
    {
        if (! is_string($caminho) || trim($caminho) === '') {
            throw new ExcecaoInter("Informe o caminho do {$rotulo} do Banco Inter em {$variavel}.");
        }

        $caminho = trim($caminho);

        if (! str_starts_with($caminho, '/')) {
            $caminho = base_path($caminho);
        }

        if (! is_file($caminho) || ! is_readable($caminho)) {
            throw new ExcecaoInter("O {$rotulo} do Banco Inter não foi encontrado ou não pode ser lido em {$caminho}.");
        }

        return $caminho;
    }
}
