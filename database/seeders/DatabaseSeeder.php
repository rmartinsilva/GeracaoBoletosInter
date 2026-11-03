<?php

namespace Database\Seeders;

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
