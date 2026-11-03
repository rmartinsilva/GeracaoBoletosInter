<?php

namespace Database\Seeders;

use App\Models\ConfiguracaoUsuario;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

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
                'preferencias' => [],
            ]);
        }

        $this->command->info('Configurações iniciais criadas para '.$usuarios->count().' usuários');
    }
}
