<?php

namespace App\Http\Controllers\Painel;

use App\Services\UsuarioService;
use App\Models\Grupo;
use Illuminate\Http\Request;

class UsuarioController extends PainelController
{
    public function __construct(
        protected Request $request,
        protected UsuarioService $usuarioService
    ) {
        parent::__construct($request);
    }

    /**
     * Lista todos os usuários.
     */
    public function index()
    {
        $search = $this->request->get('search');

        if ($search) {
            $usuarios = $this->usuarioService->buscarUsuarios($search, 15);
        } else {
            $usuarios = $this->usuarioService->listarUsuarios(15);
        }

        return view('painel.usuarios.index', [
            'titulo' => 'Usuários',
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'usuarios' => $usuarios,
            'search' => $search,
        ]);
    }

    /**
     * Exibe o formulário de criação.
     */
    public function create()
    {
        $grupos = Grupo::orderBy('descricao', 'asc')->get();

        return view('painel.usuarios.form', [
            'titulo' => 'Novo Usuário',
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'usuarioEdit' => null,
            'grupos' => $grupos,
        ]);
    }

    /**
     * Salva um novo usuário.
     */
    public function store()
    {
        // Validação
        $this->request->validate([
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:usuarios,login',
            'password' => 'required|string|min:6',
            'grupos' => 'array',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'login.required' => 'O login é obrigatório.',
            'login.unique' => 'Este login já está em uso.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ]);

        try {
            $this->usuarioService->criarUsuario([
                'name' => $this->request->get('name'),
                'login' => $this->request->get('login'),
                'password' => $this->request->get('password'),
            ], $this->request->get('grupos', []));

            return redirect('/painel/usuarios')
                ->with('success', 'Usuário criado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao criar usuário: ' . $e->getMessage()]);
        }
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(int $id)
    {
        $usuarioEdit = $this->usuarioService->buscarPorId($id);

        if (!$usuarioEdit) {
            return redirect('/painel/usuarios')
                ->withErrors(['error' => 'Usuário não encontrado.']);
        }

        $grupos = Grupo::orderBy('descricao', 'asc')->get();

        return view('painel.usuarios.form', [
            'titulo' => 'Editar Usuário',
            'usuario' => $this->usuario,
            'listaPermissoes' => $this->listaPermissoes,
            'usuarioEdit' => $usuarioEdit,
            'grupos' => $grupos,
        ]);
    }

    /**
     * Atualiza um usuário existente.
     */
    public function update(int $id)
    {
        // Validação
        $this->request->validate([
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:usuarios,login,' . $id,
            'password' => 'nullable|string|min:6',
            'grupos' => 'array',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'login.required' => 'O login é obrigatório.',
            'login.unique' => 'Este login já está em uso.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ]);

        try {
            $this->usuarioService->atualizarUsuario($id, [
                'name' => $this->request->get('name'),
                'login' => $this->request->get('login'),
                'password' => $this->request->get('password'),
            ], $this->request->get('grupos', []));

            return redirect('/painel/usuarios')
                ->with('success', 'Usuário atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar usuário: ' . $e->getMessage()]);
        }
    }

    /**
     * Deleta um usuário.
     */
    public function destroy(int $id)
    {
        try {
            $deleted = $this->usuarioService->deletarUsuario($id);

            if ($deleted) {
                return redirect('/painel/usuarios')
                    ->with('success', 'Usuário deletado com sucesso!');
            }

            return redirect('/painel/usuarios')
                ->withErrors(['error' => 'Usuário não encontrado.']);
        } catch (\Exception $e) {
            return redirect('/painel/usuarios')
                ->withErrors(['error' => 'Erro ao deletar usuário: ' . $e->getMessage()]);
        }
    }
}
