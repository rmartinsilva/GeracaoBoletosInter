<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoUsuario extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grupo_usuario';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'grupo_id',
        'usuario_id',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the grupo that owns the grupo_usuario.
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    /**
     * Get the usuario that owns the grupo_usuario.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
} 