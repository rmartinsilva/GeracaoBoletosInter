<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dados que a API de cobrança exige do pagador. Ficam opcionais no cadastro
     * e são conferidos na hora de emitir o boleto.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('tipo_pessoa', 20)->nullable()->after('nome_contato');
            $table->string('cpf_cnpj', 14)->nullable()->after('tipo_pessoa');
            $table->string('cep', 8)->nullable()->after('cpf_cnpj');
            $table->string('endereco')->nullable()->after('cep');
            $table->string('numero', 20)->nullable()->after('endereco');
            $table->string('complemento', 100)->nullable()->after('numero');
            $table->string('bairro', 100)->nullable()->after('complemento');
            $table->string('cidade', 100)->nullable()->after('bairro');
            $table->string('uf', 2)->nullable()->after('cidade');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_pessoa',
                'cpf_cnpj',
                'cep',
                'endereco',
                'numero',
                'complemento',
                'bairro',
                'cidade',
                'uf',
            ]);
        });
    }
};
