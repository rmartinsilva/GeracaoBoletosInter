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
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assinatura_id')->constrained('assinaturas')->cascadeOnDelete();
            $table->date('competencia');
            $table->decimal('valor', 10, 2);
            $table->date('vencimento');
            $table->string('situacao', 20)->default('aberto')->index();
            $table->timestamp('pago_em')->nullable();
            $table->timestamps();

            $table->unique(['assinatura_id', 'competencia']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lancamentos');
    }
};
