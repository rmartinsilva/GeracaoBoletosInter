<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'descricao',
    ];

    /**
     * Os usuários que pertencem a este grupo.
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'grupo_usuario');
    }

    /**
     * Os acessos permitidos para este grupo.
     */
    public function acessos(): BelongsToMany
    {
        return $this->belongsToMany(Acesso::class, 'acesso_grupo');
    }
}
