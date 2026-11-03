<?php

use App\Http\Controllers\Painel;
use App\Http\Controllers\Site;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {

    Route::get('/', [Site\SiteController::class, 'index'])->name('site.index');

    /* Autenticação Painel */
    Route::controller(Painel\AutenticacaoController::class)->group(function () {
        Route::get('painel/login', 'index')->name('login');
        Route::post('painel/login', 'login')->name('loginpainel');
        Route::get('painel/logout', 'logout');
    });
    /* Autenticação Painel */

    /* Rotas Protegidas do Painel */
    Route::prefix('painel')->middleware('auth:painel')->group(function () {
        Route::get('/', [Painel\AutenticacaoController::class, 'principal'])->name('painel.dashboard');

        // Configurações do usuário (AJAX)
        Route::controller(Painel\ConfiguracaoController::class)->group(function () {
            Route::post('/configuracao/toggle-tema', 'toggleTema')->name('configuracao.toggle-tema');
            Route::get('/configuracao', 'getConfiguracao')->name('configuracao.get');
            Route::post('/configuracao', 'setConfiguracao')->name('configuracao.set');
        });

        // Usuários
        Route::controller(Painel\UsuarioController::class)->group(function () {
            Route::get('/usuarios', 'index')->name('usuarios.index');
            Route::get('/usuarios/criar', 'create')->name('usuarios.create');
            Route::post('/usuarios', 'store')->name('usuarios.store');
            Route::get('/usuarios/{id}/editar', 'edit')->name('usuarios.edit');
            Route::put('/usuarios/{id}', 'update')->name('usuarios.update');
            Route::delete('/usuarios/{id}', 'destroy')->name('usuarios.destroy');
        });

        // Clientes
        Route::controller(Painel\ClienteController::class)->group(function () {
            Route::get('/clientes', 'index')->name('clientes.index');
            Route::get('/clientes/criar', 'create')->name('clientes.create');
            Route::post('/clientes', 'store')->name('clientes.store');
            Route::get('/clientes/{id}/editar', 'edit')->whereNumber('id')->name('clientes.edit');
            Route::put('/clientes/{id}', 'update')->whereNumber('id')->name('clientes.update');
            Route::delete('/clientes/{id}', 'destroy')->whereNumber('id')->name('clientes.destroy');
            Route::get('/clientes/{id}', 'show')->whereNumber('id')->name('clientes.show');
        });

        Route::controller(Painel\EmailCobrancaController::class)->group(function () {
            Route::get('/cobranca/email', 'edit')->name('cobranca.email.edit');
            Route::put('/cobranca/email', 'update')->name('cobranca.email.update');
        });

        // Assinaturas e lançamentos do cliente
        Route::controller(Painel\AssinaturaController::class)->group(function () {
            Route::get('/clientes/{cliente}/assinaturas/criar', 'create')->whereNumber('cliente')->name('assinaturas.create');
            Route::post('/clientes/{cliente}/assinaturas', 'store')->whereNumber('cliente')->name('assinaturas.store');
            Route::get('/clientes/{cliente}/assinaturas/{assinatura}/editar', 'edit')->whereNumber('cliente')->whereNumber('assinatura')->name('assinaturas.edit');
            Route::put('/clientes/{cliente}/assinaturas/{assinatura}', 'update')->whereNumber('cliente')->whereNumber('assinatura')->name('assinaturas.update');
            Route::delete('/clientes/{cliente}/assinaturas/{assinatura}', 'destroy')->whereNumber('cliente')->whereNumber('assinatura')->name('assinaturas.destroy');
            Route::get('/clientes/{cliente}/assinaturas/{assinatura}', 'show')->whereNumber('cliente')->whereNumber('assinatura')->name('assinaturas.show');
            Route::post('/clientes/{cliente}/assinaturas/{assinatura}/lancamentos', 'gerarProximo')->whereNumber('cliente')->whereNumber('assinatura')->name('lancamentos.store');
            Route::put('/clientes/{cliente}/assinaturas/{assinatura}/lancamentos/{lancamento}', 'atualizarLancamento')->whereNumber('cliente')->whereNumber('assinatura')->whereNumber('lancamento')->name('lancamentos.update');
            Route::post('/clientes/{cliente}/assinaturas/{assinatura}/lancamentos/{lancamento}/pagamento', 'registrarPagamento')->whereNumber('cliente')->whereNumber('assinatura')->whereNumber('lancamento')->name('lancamentos.pagamento');
            Route::post('/clientes/{cliente}/assinaturas/{assinatura}/lancamentos/{lancamento}/cancelar', 'cancelarLancamento')->whereNumber('cliente')->whereNumber('assinatura')->whereNumber('lancamento')->name('lancamentos.cancelar');
        });
    });
    /* Rotas Protegidas do Painel */
});
