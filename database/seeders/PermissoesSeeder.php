<?php

namespace Database\Seeders;

use App\Models\Acesso;
use App\Models\Grupo;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Cria o grupo Administrador com todas as permissões e o usuário admin.
 *
 * Pode rodar de novo num banco já populado: cadastra só as permissões que faltam,
 * vincula ao Administrador e não altera a senha do admin.
 */
class PermissoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grupoAdmin = Grupo::query()->firstOrCreate(['descricao' => 'Administrador']);

        $acessos = [
            // Clientes
            ['descricao' => 'Visualizar Clientes', 'menu' => 'Clientes', 'key' => 'cliente_visualizar'],
            ['descricao' => 'Cadastrar Clientes', 'menu' => 'Clientes', 'key' => 'cliente_cadastrar'],
            ['descricao' => 'Editar Clientes', 'menu' => 'Clientes', 'key' => 'cliente_editar'],
            ['descricao' => 'Deletar Clientes', 'menu' => 'Clientes', 'key' => 'cliente_deletar'],

            // Usuários
            ['descricao' => 'Visualizar Usuários', 'menu' => 'Usuários', 'key' => 'usuario_visualizar'],
            ['descricao' => 'Cadastrar Usuários', 'menu' => 'Usuários', 'key' => 'usuario_cadastrar'],
            ['descricao' => 'Editar Usuários', 'menu' => 'Usuários', 'key' => 'usuario_editar'],
            ['descricao' => 'Deletar Usuários', 'menu' => 'Usuários', 'key' => 'usuario_deletar'],
        ];

        $acessoIds = [];
        foreach ($acessos as $acesso) {
            $acessoIds[] = Acesso::query()->updateOrCreate(
                ['key' => $acesso['key']],
                ['descricao' => $acesso['descricao'], 'menu' => $acesso['menu']],
            )->id;
        }

        $grupoAdmin->acessos()->syncWithoutDetaching($acessoIds);

        // Login: admin / Senha: admin123
        $usuarioAdmin = Usuario::query()->firstOrCreate(
            ['login' => 'admin'],
            ['name' => 'Administrador', 'password' => Hash::make('admin123')],
        );

        $usuarioAdmin->grupos()->syncWithoutDetaching([$grupoAdmin->id]);
    }
}
