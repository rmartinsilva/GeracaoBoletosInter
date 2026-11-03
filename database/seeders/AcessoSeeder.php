<?php

namespace Database\Seeders;

use App\Models\Acesso;
use Illuminate\Database\Seeder;

class AcessoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissoes = [
            ['key' => 'cliente_visualizar', 'descricao' => 'Visualizar Clientes', 'menu' => 'Clientes'],
            ['key' => 'cliente_cadastrar', 'descricao' => 'Cadastrar Clientes', 'menu' => 'Clientes'],
            ['key' => 'cliente_editar', 'descricao' => 'Editar Clientes', 'menu' => 'Clientes'],
            ['key' => 'cliente_deletar', 'descricao' => 'Deletar Clientes', 'menu' => 'Clientes'],
            ['key' => 'usuario_visualizar', 'descricao' => 'Visualizar Usuários', 'menu' => 'Usuários'],
            ['key' => 'usuario_cadastrar', 'descricao' => 'Cadastrar Usuários', 'menu' => 'Usuários'],
            ['key' => 'usuario_editar', 'descricao' => 'Editar Usuários', 'menu' => 'Usuários'],
            ['key' => 'usuario_deletar', 'descricao' => 'Deletar Usuários', 'menu' => 'Usuários'],
        ];

        foreach ($permissoes as $permissao) {
            Acesso::updateOrCreate(
                ['key' => $permissao['key']], // Cria ou atualiza baseado na chave
                [
                    'descricao' => $permissao['descricao'],
                    'menu' => $permissao['menu'],
                ]
            );
        }
    }
}
