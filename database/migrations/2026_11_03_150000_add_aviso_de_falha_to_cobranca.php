<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Destinatário do aviso de falha e o instante em que esse aviso já foi enviado.
     */
    public function up(): void
    {
        Schema::table('configuracao_cobranca', function (Blueprint $table) {
            $table->string('email_falha')->nullable()->after('corpo_email');
        });

        Schema::table('lancamentos', function (Blueprint $table) {
            $table->timestamp('falha_notificada_em')->nullable()->after('erro_emissao');
        });
    }

    public function down(): void
    {
        Schema::table('configuracao_cobranca', function (Blueprint $table) {
            $table->dropColumn('email_falha');
        });

        Schema::table('lancamentos', function (Blueprint $table) {
            $table->dropColumn('falha_notificada_em');
        });
    }
};
