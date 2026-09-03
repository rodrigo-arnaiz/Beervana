<?php

namespace App\Console\Commands;

use App\Services\MercadoPago;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Verifica que las credenciales de Mercado Pago sirvan, sin tocar la base.
 *
 * Existe porque la pregunta "¿mi token anda?" no se contesta leyendo
 * documentación: se contesta pidiéndole algo a MP y viendo qué responde. Crea
 * una preferencia de juguete y la reporta. No genera ningún pedido ni cobro.
 */
class ProbarMercadoPago extends Command
{
    protected $signature = 'mp:probar';

    protected $description = 'Prueba las credenciales de Mercado Pago contra su API';

    public function handle(): int
    {
        $modo = config('services.mercadopago.modo');
        $token = config('services.mercadopago.access_token');

        $this->newLine();
        $this->line('  <options=bold>Configuración</>');
        $this->line('  modo   : '.($modo ?: 'real'));

        if ($modo === 'simulado') {
            $this->newLine();
            $this->warn('  Estás en modo simulado: no se llama a Mercado Pago.');
            $this->line('  Poné MERCADOPAGO_MODO=real en el .env para probar el token de verdad.');
            $this->newLine();

            return self::SUCCESS;
        }

        if (blank($token)) {
            $this->newLine();
            $this->error('  Falta MERCADOPAGO_ACCESS_TOKEN en el .env.');
            $this->newLine();

            return self::FAILURE;
        }

        $this->line('  token  : '.substr($token, 0, 12).'…'.substr($token, -4));
        $this->newLine();

        // De quién es la cuenta importa más que el prefijo del token: una
        // cuenta de prueba usa credenciales con formato de producción, y aun
        // así no mueve un peso. Mirar solo el prefijo diría "PRODUCCIÓN" y
        // asustaría al pedo.
        $this->line('  <options=bold>La cuenta detrás del token</>');

        $quien = Http::withToken($token)->acceptJson()->timeout(15)
            ->get('https://api.mercadopago.com/users/me');

        if ($quien->failed()) {
            $this->error('  No se pudo consultar la cuenta: HTTP '.$quien->status());
            $this->line('  Suele ser token mal copiado. Fijate que esté entero.');
            $this->newLine();

            return self::FAILURE;
        }

        $apodo = (string) $quien->json('nickname');
        $esCuentaDePrueba = str_starts_with($apodo, 'TEST');
        $estado = $quien->json('status', []);

        $this->line('  id      : '.$quien->json('id'));
        $this->line('  usuario : '.$apodo);
        $this->line('  país    : '.$quien->json('site_id'));
        $this->newLine();

        if ($esCuentaDePrueba) {
            $this->info('  ✔ Es una cuenta de prueba. Nada de lo que pase acá es real.');
        } elseif (str_starts_with($token, 'TEST-')) {
            $this->warn('  ⚠ Cuenta REAL con credenciales de prueba.');
            $this->line('    MP no procesa pagos de prueba contra una cuenta real: exige que');
            $this->line('    vendedor y comprador sean del mismo tipo. Para cobrar de prueba,');
            $this->line('    el vendedor tiene que ser un usuario de prueba (mp:usuarios-prueba).');
        } else {
            $this->error('  ⚠ Cuenta REAL con credenciales de PRODUCCIÓN: los pagos son de verdad.');
        }

        // address_pending y compañía bloquean cosas y el error que se ve después
        // no menciona la causa por ningún lado.
        foreach (['billing', 'list'] as $area) {
            if (($estado[$area]['allow'] ?? true) === false) {
                $this->newLine();
                $this->warn('  ⚠ La cuenta tiene '.$area.' bloqueado: '
                    .implode(', ', $estado[$area]['codes'] ?? []));
            }
        }

        $this->newLine();

        $this->line('  <options=bold>Pidiéndole una preferencia de prueba a Mercado Pago…</>');

        // Una preferencia mínima, con un ítem inventado. No queda asociada a
        // ningún pedido ni cobra nada: solo prueba que el token sea aceptado.
        $respuesta = Http::withToken($token)
            ->acceptJson()
            ->timeout(15)
            ->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [[
                    'title' => 'Prueba de credenciales Beervana',
                    'quantity' => 1,
                    'unit_price' => 100.0,
                    'currency_id' => config('services.mercadopago.moneda'),
                ]],
                'external_reference' => 'PRUEBA-CREDENCIALES',
            ]);

        $this->newLine();

        if ($respuesta->successful()) {
            $this->info('  ✔ Las credenciales funcionan.');
            $this->newLine();
            $this->line('  preferencia : '.$respuesta->json('id'));
            $this->line('  checkout    : '.$respuesta->json('init_point'));
            $this->newLine();
            $this->line('  <options=bold>El token sirve para Checkout Pro.</> El modelo de integración que');
            $this->line('  elegiste en el panel no limita qué endpoints podés llamar.');
            $this->newLine();
            $this->comment('  Podés abrir ese link para ver el checkout real.');
            $this->newLine();

            return self::SUCCESS;
        }

        $this->error('  ✘ Mercado Pago rechazó la llamada.');
        $this->newLine();
        $this->line('  HTTP    : '.$respuesta->status());
        $this->line('  mensaje : '.($respuesta->json('message') ?? 'sin mensaje'));

        if ($respuesta->json('cause')) {
            $this->line('  causa   : '.json_encode($respuesta->json('cause'), JSON_UNESCAPED_UNICODE));
        }

        $this->newLine();

        // Los dos errores que más se ven, con qué significan de verdad
        if ($respuesta->status() === 401) {
            $this->line('  401 es token inválido o mal copiado. Fijate que esté entero');
            $this->line('  y que sea el Access Token, no la Public Key.');
        }

        if ($respuesta->status() === 400) {
            $this->line('  400 es que el token sirve pero algo del cuerpo no le gustó.');
            $this->line('  Revisá que MERCADOPAGO_MONEDA sea la de tu país (ARS, MXN, BRL…).');
        }

        $this->newLine();

        return self::FAILURE;
    }
}
