<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\ConfiguracaoUsuario;

class ConfiguracaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Busca todos os usuários que não têm configuração
        $usuarios = Usuario::whereDoesntHave('configuracao')->get();

        foreach ($usuarios as $usuario) {
            ConfiguracaoUsuario::create([
                'usuario_id' => $usuario->id,
                'tema' => 'claro', // Define tema claro como padrão
                'preferencias' => []
            ]);
        }

        $this->command->info('Configurações iniciais criadas para ' . $usuarios->count() . ' usuários');
    }
}
