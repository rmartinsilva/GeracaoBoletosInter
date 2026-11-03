<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A cobrança avulsa deixa de ser uma assinatura e passa a ser um lançamento do cliente.
     */
    public function up(): void
    {
        Schema::table('lancamentos', function (Blueprint $table) {
            $table->dropForeign(['assinatura_id']);
        });

        Schema::table('lancamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('assinatura_id')->nullable()->change();
            $table->foreign('assinatura_id')->references('id')->on('assinaturas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->after('id')->constrained('clientes')->restrictOnDelete();
            $table->string('descricao')->nullable()->after('cliente_id');
        });

        $avulsas = DB::table('assinaturas')->where('periodicidade', 'avulso')->get();

        foreach ($avulsas as $assinatura) {
            DB::table('lancamentos')->where('assinatura_id', $assinatura->id)->update([
                'cliente_id' => $assinatura->cliente_id,
                'descricao' => $assinatura->descricao,
                'assinatura_id' => null,
            ]);

            DB::table('assinaturas')->where('id', $assinatura->id)->delete();
        }
    }

    public function down(): void
    {
        DB::table('lancamentos')->whereNull('assinatura_id')->delete();

        Schema::table('lancamentos', function (Blueprint $table) {
            $table->dropForeign(['assinatura_id']);
            $table->dropConstrainedForeignId('cliente_id');
            $table->dropColumn('descricao');
        });

        Schema::table('lancamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('assinatura_id')->nullable(false)->change();
            $table->foreign('assinatura_id')->references('id')->on('assinaturas')->cascadeOnDelete();
        });
    }
};
