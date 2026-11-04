<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoCobranca extends Model
{
    public const CORPO_PADRAO = <<<'TXT'
Olá, {nome}.

Segue a cobrança de {descricao}, no valor de {valor}, com vencimento em {vencimento}.

Linha digitável:
{linha_digitavel}

Pix copia e cola:
{pix}

O boleto está em anexo.
TXT;

    protected $table = 'configuracao_cobranca';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'corpo_email',
        'email_falha',
        'email_copia',
    ];

    /**
     * A única linha de configuração. Cria o texto padrão se a tabela estiver vazia.
     */
    public static function atual(): self
    {
        $existente = self::query()->first();

        if ($existente !== null) {
            return $existente;
        }

        return self::query()->create([
            'corpo_email' => self::CORPO_PADRAO,
        ]);
    }
}
