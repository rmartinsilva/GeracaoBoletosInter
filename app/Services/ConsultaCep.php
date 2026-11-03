<?php

namespace App\Services;

use App\Exceptions\RegraNegocioException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Consulta o endereço de um CEP no ViaCEP.
 */
class ConsultaCep
{
    private const URL = 'https://viacep.com.br/ws/%s/json/';

    /**
     * @return array{cep: string, endereco: string, bairro: string, cidade: string, uf: string}
     */
    public function buscar(string $cep): array
    {
        $digitos = preg_replace('/\D/', '', $cep) ?? '';

        if (strlen($digitos) !== 8) {
            throw new RegraNegocioException('Informe um CEP com 8 dígitos.');
        }

        try {
            $resposta = Http::timeout(5)
                ->acceptJson()
                ->get(sprintf(self::URL, $digitos));
        } catch (ConnectionException) {
            throw new RegraNegocioException('Não foi possível consultar o CEP agora.');
        }

        $corpo = $resposta->json();

        if (! $resposta->successful() || ! is_array($corpo) || ($corpo['erro'] ?? false) === true) {
            throw new RegraNegocioException('CEP não encontrado.');
        }

        return [
            'cep' => $digitos,
            'endereco' => $this->texto($corpo['logradouro'] ?? null),
            'bairro' => $this->texto($corpo['bairro'] ?? null),
            'cidade' => $this->texto($corpo['localidade'] ?? null),
            'uf' => strtoupper($this->texto($corpo['uf'] ?? null)),
        ];
    }

    private function texto(mixed $valor): string
    {
        return is_string($valor) ? trim($valor) : '';
    }
}
