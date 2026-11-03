<?php

namespace App\Providers;

use App\Repositories\Contracts\AssinaturaRepositoryInterface;
use App\Repositories\Contracts\ClienteRepositoryInterface;
use App\Repositories\Contracts\LancamentoRepositoryInterface;
use App\Repositories\Contracts\UsuarioRepositoryInterface;
use App\Repositories\Eloquent\AssinaturaRepository;
use App\Repositories\Eloquent\ClienteRepository;
use App\Repositories\Eloquent\LancamentoRepository;
use App\Repositories\Eloquent\UsuarioRepository;
use App\Services\Inter\CobrancaInterService;
use App\Services\Inter\ConfiguracaoInter;
use App\Services\Inter\Contracts\CobrancaInterInterface;
use App\Services\Inter\Contracts\WebhookCobrancaInterInterface;
use App\Services\Inter\WebhookCobrancaInterService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Repository Interfaces to Implementations
        $this->app->bind(
            UsuarioRepositoryInterface::class,
            UsuarioRepository::class
        );

        $this->app->bind(
            ClienteRepositoryInterface::class,
            ClienteRepository::class
        );

        $this->app->bind(
            AssinaturaRepositoryInterface::class,
            AssinaturaRepository::class
        );

        $this->app->bind(
            LancamentoRepositoryInterface::class,
            LancamentoRepository::class
        );

        $this->app->singleton(ConfiguracaoInter::class, fn (): ConfiguracaoInter => ConfiguracaoInter::fromConfig());

        $this->app->bind(CobrancaInterInterface::class, CobrancaInterService::class);
        $this->app->bind(WebhookCobrancaInterInterface::class, WebhookCobrancaInterService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
