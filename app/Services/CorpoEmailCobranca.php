<?php

namespace App\Services;

use App\Models\Assinatura;
use App\Models\Cliente;
use App\Models\Lancamento;

/**
 * Troca os marcadores do texto padrão pelos dados da cobrança emitida.
 */
class CorpoEmailCobranca
{
    public function montar(string $modelo, Cliente $cliente, Assinatura $assinatura, Lancamento $lancamento): string
    {
        return strtr($modelo, [
            '{nome}' => $cliente->nome,
            '{descricao}' => $assinatura->descricao,
            '{valor}' => 'R$ '.number_format((float) $lancamento->valor, 2, ',', '.'),
            '{vencimento}' => $lancamento->vencimento->format('d/m/Y'),
            '{linha_digitavel}' => (string) $lancamento->linha_digitavel,
            '{pix}' => $lancamento->pix_copia_cola ?: 'indisponível',
        ]);
    }
}
