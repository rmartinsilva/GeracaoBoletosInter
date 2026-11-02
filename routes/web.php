<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Painel;
use App\Http\Controllers\Site;

Route::middleware('web')->group(function () {

    /* Site */
    Route::controller(\App\Http\Controllers\Site\SiteController::class)->group(function () {
        Route::get('/', 'index')->name('site.index');
        Route::get('/sobre', 'sobre')->name('site.sobre');
        Route::get('/servicos', 'servicos')->name('site.servicos');
        Route::get('/portfolio', 'portfolio')->name('site.portfolio');
        Route::get('/contato', 'contato')->name('site.contato');
    });
    /* Site */

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
    });
    /* Rotas Protegidas do Painel */
});
