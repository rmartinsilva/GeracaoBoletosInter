<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PainelController extends Controller
{
    protected $usuario;
    protected array $listaPermissoes = [];

    /**
     * Constructor.
     */
    public function __construct(protected Request $request)
    {
        // Carregar permissões da sessão se existirem
        $this->carregarPermissoes();
    }

    /**
     * Carrega as permissões da sessão
     */
    protected function carregarPermissoes(): void
    {
        if ($this->request->session()->has('permissoes')) {
            $this->usuario = $this->getUsuarioNome();
            $permissoes = $this->request->session()->get('permissoes', []);
            $this->listaPermissoes = $permissoes;
        }
    }

    /**
     * Método para verificar permissões específicas em controllers filhos
     * Sobrescrever este método nos controllers que precisam de permissões específicas
     */
    protected function verificaPermissao(): void
    {
        // Implementação específica em cada controller filho
    }

    /**
     * Retorna o login do usuário logado
     */
    protected function getUsuarioLogin(): string
    {
        $logado = Auth::guard('painel')->user();
        return $logado->login ?? '';
    }

    /**
     * Retorna o nome do usuário logado
     */
    protected function getUsuarioNome(): string
    {
        $logado = Auth::guard('painel')->user();
        return $logado->name ?? '';
    }

    /**
     * Retorna o usuário logado completo
     */
    protected function getUsuarioLogado()
    {
        return Auth::guard('painel')->user();
    }

    /**
     * Verifica se o usuário possui uma permissão específica
     */
    protected function hasPermission(string $permissionKey): bool
    {
        return in_array($permissionKey, $this->listaPermissoes);
    }

    /**
     * Redireciona se não tiver permissão
     */
    protected function checkPermissionOrFail(string $permissionKey, string $message = 'Você não tem permissão para acessar esta funcionalidade.'): void
    {
        if (!$this->hasPermission($permissionKey)) {
            abort(403, $message);
        }
    }
}
