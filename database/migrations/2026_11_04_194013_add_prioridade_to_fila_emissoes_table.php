<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fila_emissoes', function (Blueprint $table) {
            $table->boolean('prioridade')->default(false)->after('disponivel_em');
        });
    }

    public function down(): void
    {
        Schema::table('fila_emissoes', function (Blueprint $table) {
            $table->dropColumn('prioridade');
        });
    }
};
