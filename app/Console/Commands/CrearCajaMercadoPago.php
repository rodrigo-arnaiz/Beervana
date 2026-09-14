<?php

namespace App\Console\Commands;

use App\Services\MercadoPago;
use Illuminate\Console\Command;

/**
 * Crea la sucursal y la caja que necesita el cobro con QR presencial.
 *
 * Se corre una sola vez por cuenta. El QR con monto no cuelga de la nada: MP lo
 * asocia a una caja (POS) de una sucursal, igual que en un local de verdad.
 */
class CrearCajaMercadoPago extends Command
{
    protected $signature = 'mp:caja
        {--sucursal=BEERVANA01 : ID externo de la sucursal}
        {--caja=BEERVANACAJA01 : ID externo de la caja}';

    protected $description = 'Crea la sucursal y la caja de Mercado Pago para cobrar con QR';

    public function handle(MercadoPago $mp): int
    {
        if (! $mp->estaConfigurado()) {
            $this->error('Falta MERCADOPAGO_ACCESS_TOKEN en el .env.');

            return self::FAILURE;
        }

        $this->newLine();

        try {
            $cuenta = $mp->quienSoy();
        } catch (\Throwable $e) {
            $this->error('No se pudo consultar la cuenta: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->line('  cuenta : '.$cuenta['nickname'].' (id '.$cuenta['id'].')');
        $this->newLine();

        try {
            $r = $mp->crearSucursalYCaja(
                $this->option('sucursal'),
                $this->option('caja'),
                [
                    'street_number' => '123',
                    'street_name' => 'Av. Siempreviva',
                    'city_name' => 'Córdoba',
                    'state_name' => 'Córdoba',
                    'latitude' => -31.42,
                    'longitude' => -64.18,
                ]
            );
        } catch (\Throwable $e) {
            $this->error('  No se pudo crear: '.$e->getMessage());
            $this->newLine();
            $this->line('  Si dice que ya existe, la sucursal o la caja ya estaban creadas.');
            $this->line('  Usá --sucursal y --caja con otros nombres, o tomá los ids que ya tenés.');
            $this->newLine();

            return self::FAILURE;
        }

        $this->info('  ✔ Sucursal y caja creadas.');
        $this->newLine();
        $this->line('  <options=bold>Poné esto en el .env:</>');
        $this->newLine();
        $this->line('  MERCADOPAGO_USER_ID='.$r['user_id']);
        $this->line('  MERCADOPAGO_CAJA='.$this->option('caja'));
        $this->newLine();
        $this->comment('  Y después: php artisan config:clear');
        $this->newLine();

        return self::SUCCESS;
    }
}
