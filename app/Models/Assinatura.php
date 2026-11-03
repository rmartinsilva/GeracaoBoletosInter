<?php

namespace App\Models;

use App\Enums\Periodicidade;
use App\Enums\SituacaoAssinatura;
use Database\Factories\AssinaturaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assinatura extends Model
{
    /** @use HasFactory<AssinaturaFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assinaturas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cliente_id',
        'descricao',
        'valor',
        'periodicidade',
        'dia_vencimento',
        'dia_geracao',
        'data_inicio',
        'data_fim',
        'situacao',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'periodicidade' => Periodicidade::class,
            'dia_vencimento' => 'integer',
            'dia_geracao' => 'integer',
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'situacao' => SituacaoAssinatura::class,
        ];
    }

    /**
     * Cliente dono desta assinatura.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Cobranças geradas a partir desta assinatura, uma por período.
     */
    public function lancamentos(): HasMany
    {
        return $this->hasMany(Lancamento::class);
    }
}
