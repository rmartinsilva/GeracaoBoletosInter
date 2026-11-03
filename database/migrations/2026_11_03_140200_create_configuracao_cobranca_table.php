<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Texto único, usado em todo e-mail de cobrança.
     */
    public function up(): void
    {
        Schema::create('configuracao_cobranca', function (Blueprint $table) {
            $table->id();
            $table->text('corpo_email');
            $table->timestamps();
        });

        DB::table('configuracao_cobranca')->insert([
            'corpo_email' => <<<'TXT'
Olá, {nome}.

Segue a cobrança de {descricao}, no valor de {valor}, com vencimento em {vencimento}.

Linha digitável:
{linha_digitavel}

Pix copia e cola:
{pix}

O boleto está em anexo.
TXT,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracao_cobranca');
    }
};
