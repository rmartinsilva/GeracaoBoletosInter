<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dados do boleto emitido no Banco Inter e o instante em que o e-mail saiu.
     */
    public function up(): void
    {
        Schema::table('lancamentos', function (Blueprint $table) {
            $table->string('codigo_solicitacao', 40)->nullable()->unique()->after('pago_em');
            $table->string('linha_digitavel', 60)->nullable()->after('codigo_solicitacao');
            $table->text('pix_copia_cola')->nullable()->after('linha_digitavel');
            $table->string('nosso_numero', 20)->nullable()->after('pix_copia_cola');
            $table->timestamp('email_enviado_em')->nullable()->after('nosso_numero');
            $table->text('erro_emissao')->nullable()->after('email_enviado_em');
        });
    }

    public function down(): void
    {
        Schema::table('lancamentos', function (Blueprint $table) {
            $table->dropColumn([
                'codigo_solicitacao',
                'linha_digitavel',
                'pix_copia_cola',
                'nosso_numero',
                'email_enviado_em',
                'erro_emissao',
            ]);
        });
    }
};
