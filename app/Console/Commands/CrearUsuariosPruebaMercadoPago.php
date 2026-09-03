<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Crea las cuentas de prueba de Mercado Pago.
 *
 * Hacen falta dos, y el motivo no es obvio: MP exige que el vendedor y el
 * comprador sean del MISMO tipo. Con una cuenta real como vendedora, cualquier
 * pago de prueba se rechaza —con "una de las partes es de prueba" si el
 * comprador está logueado, o con "no pudimos procesar tu pago" si paga como
 * invitado con tarjeta de prueba—. Las dos cosas son el mismo problema.
 *
 * Ojo: las cuentas de prueba NO se pueden borrar y hay un tope de 15 por
 * cuenta, así que conviene no correr esto de más.
 */
class CrearUsuariosPruebaMercadoPago extends Command
{
    protected $signature = 'mp:usuarios-prueba {--site=MLA : País de las cuentas (MLA Argentina, MLM México, MLB Brasil)}';

    protected $description = 'Crea un vendedor y un comprador de prueba en Mercado Pago';

    public function handle(): int
    {
        $token = config('services.mercadopago.access_token');

        if (blank($token)) {
            $this->error('Falta MERCADOPAGO_ACCESS_TOKEN en el .env.');

            return self::FAILURE;
        }

        $site = $this->option('site');

        $this->newLine();
        $this->warn('  Las cuentas de prueba no se pueden borrar y hay un tope de 15.');

        if (! $this->confirm('  ¿Crear un vendedor y un comprador de prueba?', true)) {
            return self::SUCCESS;
        }

        $cuentas = [];

        foreach (['vendedor', 'comprador'] as $rol) {
            $this->newLine();
            $this->line("  Creando el <options=bold>{$rol}</>…");

            $r = Http::withToken($token)->acceptJson()->timeout(20)
                ->post('https://api.mercadopago.com/users/test_user', ['site_id' => $site]);

            if ($r->failed()) {
                $this->error('  No se pudo crear: HTTP '.$r->status().' — '.($r->json('message') ?? ''));

                return self::FAILURE;
            }

            $cuentas[$rol] = $r->json();
            $this->info('  ✔ listo');
        }

        $this->newLine(2);
        $this->line('  <options=bold>Guardá esto: MP no te lo vuelve a mostrar.</>');
        $this->newLine();

        foreach ($cuentas as $rol => $c) {
            $this->line('  ── '.strtoupper($rol).' ──');
            $this->line('  id       : '.$c['id']);
            $this->line('  usuario  : '.$c['nickname']);
            $this->line('  email    : '.$c['email']);
            $this->line('  password : '.$c['password']);
            $this->newLine();
        }

        $this->line('  <options=bold>Qué hacer ahora</>');
        $this->line('  1. Entrá a mercadopago.com.ar con el VENDEDOR (en incógnito).');
        $this->line('  2. Andá a developers → Tus integraciones → Crear aplicación.');
        $this->line('  3. Copiá SU Access Token al .env como MERCADOPAGO_ACCESS_TOKEN.');
        $this->line('  4. Poné MERCADOPAGO_USAR_SANDBOX=true');
        $this->line('  5. php artisan config:clear && php artisan mp:probar');
        $this->line('  6. Pagá logueado con el COMPRADOR, en otra ventana de incógnito.');
        $this->newLine();

        return self::SUCCESS;
    }
}
