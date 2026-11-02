<?php

namespace App\Http\Controllers\Painel;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AutenticacaoController extends PainelController
{
    /**
     * Exibe a página de login
     */
    public function index()
    {
        return view('painel.login');
    }

    /**
     * Processa o login
     */
    public function login(Request $request)
    {
        $dadosForm = $request->all();

        $validator = validator($dadosForm, [
            'txtEmail' => 'required',
            'txtSenha' => 'required',
        ], [
            'txtEmail.required' => 'O campo login é obrigatório.',
            'txtSenha.required' => 'O campo senha é obrigatório.',
        ]);

        if ($validator->fails()) {
            return redirect('painel/login')
                ->withErrors($validator)
                ->withInput($dadosForm);
        }

        $relembrar = false;
        if ($request->get('relembrarsenha') == "Sim") {
            $relembrar = true;
        }

        if (Auth::guard('painel')->attempt([
            'login' => $request->get('txtEmail'),
            'password' => $request->get('txtSenha')
        ], $relembrar)) {
            return redirect('/painel');
        } else {
            return redirect('painel/login')
                ->withInput($dadosForm)
                ->withErrors(['errors' => 'Usuário ou Senha Inválidos!']);
        }
    }

    /**
     * Página principal do painel (dashboard)
     */
    public function principal()
    {
        $listaPermissoes = [];
        $primeiroAcesso = false;

        if (!$this->request->session()->has('permissoes')) {
            $primeiroAcesso = true;
            $userLogado = Auth::guard('painel')->user();
            $permissoes = [];

            if ($userLogado instanceof Usuario) {
                $permissoes = $userLogado->acessos();
            }

            $listaPermissoes = $permissoes;
            $this->listaPermissoes = $listaPermissoes;
            $usuario = $this->getUsuarioNome();

            $this->request->session()->put('permissoes', $listaPermissoes);
        }

        if ($primeiroAcesso == false) {
            $listaPermissoes = $this->listaPermissoes;
            $usuario = $this->usuario;
        }

        return view('painel.principal', compact(['usuario', 'listaPermissoes']));
    }

    /**
     * Logout do sistema
     */
    public function logout()
    {
        Auth::guard("painel")->logout();
        $this->request->session()->flush();
        return redirect('/');
    }
}
