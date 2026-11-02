<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Acesso;
use App\Models\Grupo;
use App\Models\Usuario;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissoesSeeder::class,
        ]);

        $this->command->info('Dados iniciais criados com sucesso!');
        $this->command->info('Login: admin / Senha: admin123');
    }
}
