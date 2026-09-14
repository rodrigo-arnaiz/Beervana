<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Con MERCADOPAGO_MODO=simulado, todo el que pida un MercadoPago recibe
        // el de mentira. Nada más en la app se entera del cambio: los
        // controladores, el webhook y las pantallas son los mismos, así que
        // probar en simulado sirve para confiar en el modo real.
        $this->app->bind(\App\Services\MercadoPago::class, function () {
            return config('services.mercadopago.modo') === 'simulado'
                ? new \App\Services\MercadoPagoSimulado()
                : new \App\Services\MercadoPago();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        RedirectIfAuthenticated::redirectUsing(
            fn ($request) => $request->user()?->paginaInicial() ?? route('login')
        );
    }
}
