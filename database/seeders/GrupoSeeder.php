<?php

namespace Database\Seeders;

use App\Models\Grupo;
use Illuminate\Database\Seeder;

class GrupoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Grupo::updateOrCreate(
            ['descricao' => 'admin'],
            ['descricao' => 'admin'] // Apenas para garantir que exista
        );
    }
}
