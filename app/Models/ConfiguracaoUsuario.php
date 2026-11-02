<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracaoUsuario extends Model
{
    protected $table = 'configuracao_usuario';

    protected $fillable = [
        'usuario_id',
        'tema',
        'preferencias'
    ];

    protected $casts = [
        'preferencias' => 'array'
    ];

    /**
     * Relacionamento com o usuário
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Verifica se o usuário está usando o tema escuro
     */
    public function isDarkMode(): bool
    {
        return $this->tema === 'escuro';
    }

    /**
     * Retorna o tema atual
     */
    public function getTema(): string
    {
        return $this->tema;
    }

    /**
     * Define o tema do usuário
     */
    public function setTema(string $tema): void
    {
        if (!in_array($tema, ['claro', 'escuro'])) {
            throw new \InvalidArgumentException('Tema deve ser "claro" ou "escuro"');
        }

        $this->update(['tema' => $tema]);
    }

    /**
     * Retorna uma configuração específica
     */
    public function getPreferencia(string $chave, $valorPadrao = null)
    {
        return $this->preferencias[$chave] ?? $valorPadrao;
    }

    /**
     * Define uma configuração específica
     */
    public function setPreferencia(string $chave, $valor): void
    {
        $preferencias = $this->preferencias ?? [];
        $preferencias[$chave] = $valor;

        $this->update(['preferencias' => $preferencias]);
    }
}
