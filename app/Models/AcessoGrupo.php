<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcessoGrupo extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'acesso_grupo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'acesso_id',
        'grupo_id',
    ];

    /**
     * Get the acesso that owns the AcessoGrupo.
     */
    public function acesso(): BelongsTo
    {
        return $this->belongsTo(Acesso::class);
    }

    /**
     * Get the grupo that owns the AcessoGrupo.
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }
} 