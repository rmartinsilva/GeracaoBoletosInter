<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fila_emissoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lancamento_id')->unique()->constrained('lancamentos')->cascadeOnDelete();
            $table->timestamp('disponivel_em');
            $table->timestamp('reservado_em')->nullable();
            $table->timestamp('processado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fila_emissoes');
    }
};
