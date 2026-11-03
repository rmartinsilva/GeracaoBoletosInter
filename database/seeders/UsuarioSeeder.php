<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Usuario::updateOrCreate(
            ['login' => 'admin'], // Cria ou atualiza baseado no login
            [
                'name' => 'Administrador',
                'login' => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );
    }
}
