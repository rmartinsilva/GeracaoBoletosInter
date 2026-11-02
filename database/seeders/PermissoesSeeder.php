<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PermissoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar Grupo Administrador
        $grupoAdmin = DB::table('grupos')->insertGetId([
            'descricao' => 'Administrador',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Criar Acessos/Permissões básicas
        $acessos = [
            // Usuários
            ['descricao' => 'Visualizar Usuários', 'menu' => 'Usuários', 'key' => 'usuario_visualizar'],
            ['descricao' => 'Cadastrar Usuários', 'menu' => 'Usuários', 'key' => 'usuario_cadastrar'],
            ['descricao' => 'Editar Usuários', 'menu' => 'Usuários', 'key' => 'usuario_editar'],
            ['descricao' => 'Deletar Usuários', 'menu' => 'Usuários', 'key' => 'usuario_deletar'],

            // Grupos
            ['descricao' => 'Visualizar Grupos', 'menu' => 'Grupos', 'key' => 'grupo_visualizar'],
            ['descricao' => 'Cadastrar Grupos', 'menu' => 'Grupos', 'key' => 'grupo_cadastrar'],
            ['descricao' => 'Editar Grupos', 'menu' => 'Grupos', 'key' => 'grupo_editar'],
            ['descricao' => 'Deletar Grupos', 'menu' => 'Grupos', 'key' => 'grupo_deletar'],

            // Banner Principal
            ['descricao' => 'Visualizar Banner Principal', 'menu' => 'Banner Principal', 'key' => 'bannerPrincipal_visualizar'],
            ['descricao' => 'Cadastrar Banner Principal', 'menu' => 'Banner Principal', 'key' => 'bannerPrincipal_cadastrar'],
            ['descricao' => 'Editar Banner Principal', 'menu' => 'Banner Principal', 'key' => 'bannerPrincipal_editar'],
            ['descricao' => 'Deletar Banner Principal', 'menu' => 'Banner Principal', 'key' => 'bannerPrincipal_deletar'],

            // Empresa
            ['descricao' => 'Visualizar Empresa', 'menu' => 'Empresa', 'key' => 'empresa_visualizar'],
            ['descricao' => 'Editar Empresa', 'menu' => 'Empresa', 'key' => 'empresa_editar'],

            // Clientes
            ['descricao' => 'Visualizar Clientes', 'menu' => 'Clientes', 'key' => 'clientes_visualizar'],
            ['descricao' => 'Cadastrar Clientes', 'menu' => 'Clientes', 'key' => 'clientes_cadastrar'],
            ['descricao' => 'Editar Clientes', 'menu' => 'Clientes', 'key' => 'clientes_editar'],
            ['descricao' => 'Deletar Clientes', 'menu' => 'Clientes', 'key' => 'clientes_deletar'],

            // Serviços
            ['descricao' => 'Visualizar Serviços', 'menu' => 'Serviços', 'key' => 'servicos_visualizar'],
            ['descricao' => 'Cadastrar Serviços', 'menu' => 'Serviços', 'key' => 'servicos_cadastrar'],
            ['descricao' => 'Editar Serviços', 'menu' => 'Serviços', 'key' => 'servicos_editar'],
            ['descricao' => 'Deletar Serviços', 'menu' => 'Serviços', 'key' => 'servicos_deletar'],

            // Projetos
            ['descricao' => 'Visualizar Projetos', 'menu' => 'Projetos', 'key' => 'projetos_visualizar'],
            ['descricao' => 'Cadastrar Projetos', 'menu' => 'Projetos', 'key' => 'projetos_cadastrar'],
            ['descricao' => 'Editar Projetos', 'menu' => 'Projetos', 'key' => 'projetos_editar'],
            ['descricao' => 'Deletar Projetos', 'menu' => 'Projetos', 'key' => 'projetos_deletar'],

            // Lançamentos
            ['descricao' => 'Visualizar Lançamentos', 'menu' => 'Lançamentos', 'key' => 'lancamentos_visualizar'],
            ['descricao' => 'Cadastrar Lançamentos', 'menu' => 'Lançamentos', 'key' => 'lancamentos_cadastrar'],
            ['descricao' => 'Editar Lançamentos', 'menu' => 'Lançamentos', 'key' => 'lancamentos_editar'],
            ['descricao' => 'Deletar Lançamentos', 'menu' => 'Lançamentos', 'key' => 'lancamentos_deletar'],

            // Equipe
            ['descricao' => 'Visualizar Equipe', 'menu' => 'Equipe', 'key' => 'equipe_visualizar'],
            ['descricao' => 'Cadastrar Equipe', 'menu' => 'Equipe', 'key' => 'equipe_cadastrar'],
            ['descricao' => 'Editar Equipe', 'menu' => 'Equipe', 'key' => 'equipe_editar'],
            ['descricao' => 'Deletar Equipe', 'menu' => 'Equipe', 'key' => 'equipe_deletar'],

            // Depoimentos
            ['descricao' => 'Visualizar Depoimentos', 'menu' => 'Depoimentos', 'key' => 'depoimentos_visualizar'],
            ['descricao' => 'Cadastrar Depoimentos', 'menu' => 'Depoimentos', 'key' => 'depoimentos_cadastrar'],
            ['descricao' => 'Editar Depoimentos', 'menu' => 'Depoimentos', 'key' => 'depoimentos_editar'],
            ['descricao' => 'Deletar Depoimentos', 'menu' => 'Depoimentos', 'key' => 'depoimentos_deletar'],

            // Notícias
            ['descricao' => 'Visualizar Notícias', 'menu' => 'Notícias', 'key' => 'noticias_visualizar'],
            ['descricao' => 'Cadastrar Notícias', 'menu' => 'Notícias', 'key' => 'noticias_cadastrar'],
            ['descricao' => 'Editar Notícias', 'menu' => 'Notícias', 'key' => 'noticias_editar'],
            ['descricao' => 'Deletar Notícias', 'menu' => 'Notícias', 'key' => 'noticias_deletar'],
        ];

        $acessoIds = [];
        foreach ($acessos as $acesso) {
            $acessoIds[] = DB::table('acessos')->insertGetId([
                'descricao' => $acesso['descricao'],
                'menu' => $acesso['menu'],
                'key' => $acesso['key'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Vincular todas as permissões ao grupo Administrador
        foreach ($acessoIds as $acessoId) {
            DB::table('acesso_grupo')->insert([
                'acesso_id' => $acessoId,
                'grupo_id' => $grupoAdmin,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Criar usuário administrador padrão
        // Login: admin / Senha: admin123
        $usuarioAdmin = DB::table('usuarios')->insertGetId([
            'name' => 'Administrador',
            'login' => 'admin',
            'password' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Vincular usuário admin ao grupo Administrador
        DB::table('grupo_usuario')->insert([
            'usuario_id' => $usuarioAdmin,
            'grupo_id' => $grupoAdmin,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
