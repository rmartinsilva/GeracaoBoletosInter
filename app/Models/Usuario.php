<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'login',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }


    /**
     * Os grupos aos quais o usuário pertence.
     */
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupo_usuario');
    }

    /**
     * Retorna todas as keys de permissões do usuário através de seus grupos.
     *
     * @return array<string>
     */
    public function acessos(): array
    {
        // Garante que as relações necessárias estão carregadas
        if (!$this->relationLoaded('grupos') || !$this->grupos->first()?->relationLoaded('acessos')) {
            $this->load('grupos.acessos');
        }

        $permissoes = [];

        foreach ($this->grupos as $grupo) {
            foreach ($grupo->acessos as $acesso) {
                if (!in_array($acesso->key, $permissoes)) {
                    $permissoes[] = $acesso->key;
                }
            }
        }

        return $permissoes;
    }

    /**
     * Verifica se o usuário possui uma determinada permissão através de seus grupos.
     *
     * @param string $permissionKey A chave (key) da permissão a ser verificada.
     * @return bool
     */
    public function hasPermissionTo(string $permissionKey): bool
    {
        return in_array($permissionKey, $this->acessos());
    }

    /**
     * Relacionamento com as configurações do usuário
     */
    public function configuracao(): HasOne
    {
        return $this->hasOne(ConfiguracaoUsuario::class);
    }

    /**
     * Retorna as configurações do usuário ou cria uma nova se não existir
     */
    public function getConfiguracao(): ConfiguracaoUsuario
    {
        return $this->configuracao ?? $this->configuracao()->create([
            'tema' => 'claro',
            'preferencias' => []
        ]);
    }

    /**
     * Verifica se o usuário está usando o tema escuro
     */
    public function isDarkMode(): bool
    {
        $configuracao = $this->configuracao;
        return $configuracao ? $configuracao->isDarkMode() : false;
    }

    /**
     * Retorna o tema atual do usuário
     */
    public function getTema(): string
    {
        $configuracao = $this->configuracao;
        return $configuracao ? $configuracao->getTema() : 'claro';
    }

}
