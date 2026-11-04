<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilaEmissao extends Model
{
    protected $table = 'fila_emissoes';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'lancamento_id',
        'disponivel_em',
        'reservado_em',
        'processado_em',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'disponivel_em' => 'datetime',
            'reservado_em' => 'datetime',
            'processado_em' => 'datetime',
        ];
    }

    public function lancamento(): BelongsTo
    {
        return $this->belongsTo(Lancamento::class);
    }
}
