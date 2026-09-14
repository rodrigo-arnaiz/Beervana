<?php

namespace App\Console\Commands;

use App\Models\Pedido;
use Illuminate\Console\Command;

/**
 * Marca como vencidos los pedidos pendientes cuya reserva ya caducó.
 *
 * Es higiene, NO corrección: la disponibilidad ya ignora las reservas vencidas
 * por la condición sobre expira_en, así que la app funciona bien aunque este
 * comando no corra nunca. Existe para que la tabla refleje la realidad y para
 * que el listado de pedidos no dependa de recalcular el estado en cada lectura.
 *
 * En Vercel no hay scheduler, así que se corre a mano:
 *   docker compose exec backend php artisan pedidos:limpiar
 */
class LimpiarPedidosVencidos extends Command
{
    protected $signature = 'pedidos:limpiar';

    protected $description = 'Marca como vencidos los pedidos pendientes cuya reserva ya caducó';

    public function handle(): int
    {
        $cantidad = Pedido::vencidos()->update([
            'estado' => Pedido::VENCIDO,
            'expira_en' => null,
        ]);

        $this->info($cantidad === 0
            ? 'No había pedidos vencidos.'
            : "Se marcaron {$cantidad} pedidos como vencidos.");

        return self::SUCCESS;
    }
}
