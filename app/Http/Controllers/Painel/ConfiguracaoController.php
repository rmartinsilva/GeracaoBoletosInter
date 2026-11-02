<?php

namespace App\Http\Controllers\Painel;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoUsuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ConfiguracaoController extends Controller
{
    /**
     * Toggle entre tema claro e escuro
     */
    public function toggleTema(Request $request): JsonResponse
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            // Busca ou cria configuração do usuário
            $configuracao = $usuario->getConfiguracao();

            // Determina o novo tema
            $novoTema = $configuracao->tema === 'claro' ? 'escuro' : 'claro';

            // Atualiza o tema
            $configuracao->setTema($novoTema);

            return response()->json([
                'success' => true,
                'tema' => $novoTema,
                'message' => 'Tema alterado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar tema: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna a configuração atual do usuário
     */
    public function getConfiguracao(): JsonResponse
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $configuracao = $usuario->getConfiguracao();

            return response()->json([
                'success' => true,
                'configuracao' => [
                    'tema' => $configuracao->tema,
                    'preferencias' => $configuracao->preferencias
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar configuração: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza uma configuração específica
     */
    public function setConfiguracao(Request $request): JsonResponse
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado'
                ], 401);
            }

            $configuracao = $usuario->getConfiguracao();

            // Valida os dados recebidos
            $request->validate([
                'chave' => 'required|string',
                'valor' => 'required'
            ]);

            // Atualiza a configuração
            $configuracao->setPreferencia($request->chave, $request->valor);

            return response()->json([
                'success' => true,
                'message' => 'Configuração atualizada com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar configuração: ' . $e->getMessage()
            ], 500);
        }
    }
}
