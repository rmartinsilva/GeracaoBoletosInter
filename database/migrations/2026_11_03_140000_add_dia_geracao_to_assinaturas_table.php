<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dia do mês em que a cobrança é emitida e enviada por e-mail.
     */
    public function up(): void
    {
        Schema::table('assinaturas', function (Blueprint $table) {
            $table->unsignedTinyInteger('dia_geracao')->nullable()->after('dia_vencimento');
        });
    }

    public function down(): void
    {
        Schema::table('assinaturas', function (Blueprint $table) {
            $table->dropColumn('dia_geracao');
        });
    }
};
