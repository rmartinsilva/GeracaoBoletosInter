<?php

namespace App\Models;

use App\Enums\SituacaoLancamento;
use Database\Factories\LancamentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lancamento extends Model
{
    /** @use HasFactory<LancamentoFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lancamentos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assinatura_id',
        'cliente_id',
        'descricao',
        'competencia',
        'valor',
        'vencimento',
        'situacao',
        'pago_em',
        'codigo_solicitacao',
        'linha_digitavel',
        'pix_copia_cola',
        'nosso_numero',
        'email_enviado_em',
        'erro_emissao',
        'falha_notificada_em',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'competencia' => 'date',
            'valor' => 'decimal:2',
            'vencimento' => 'date',
            'situacao' => SituacaoLancamento::class,
            'pago_em' => 'datetime',
            'email_enviado_em' => 'datetime',
            'falha_notificada_em' => 'datetime',
        ];
    }

    /**
     * Assinatura que originou esta cobrança.
     */
    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(Assinatura::class);
    }

    /**
     * Cliente da cobrança avulsa. Nas assinaturas, o cliente vem da assinatura.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
