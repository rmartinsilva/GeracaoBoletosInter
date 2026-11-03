<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assinaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->string('periodicidade', 20)->index();
            $table->unsignedTinyInteger('dia_vencimento')->nullable();
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->string('situacao', 20)->default('ativa')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assinaturas');
    }
};
