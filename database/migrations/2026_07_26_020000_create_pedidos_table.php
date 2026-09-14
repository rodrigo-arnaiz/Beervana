<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El pedido es la intención de compra; la factura es el comprobante de una
 * venta consumada. Antes estaban colapsados en `facturas`, y eso dejaba
 * "facturas" con pagada=false que documentaban ventas que nunca ocurrieron.
 *
 * Un pedido pendiente reserva stock hasta `expira_en`. Vencido ese momento la
 * reserva deja de contar sola, sin que haga falta que corra ningún proceso:
 * la disponibilidad se calcula mirando pedidos pendientes NO vencidos. Es a
 * propósito, porque en Vercel no hay dónde correr un scheduler.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // pendiente | pagado | cancelado | vencido
            $table->string('estado')->default('pendiente');

            $table->string('metodo_entrega')->default('envio'); // envio | retiro
            $table->decimal('envio', 10, 2)->default(0);
            $table->decimal('precio_total', 10, 2)->default(0);

            // Hasta cuándo vale la reserva. Null cuando el pedido ya no está
            // pendiente: no tiene sentido que un pedido pagado "venza".
            $table->timestamp('expira_en')->nullable();

            $table->timestamps();

            // El cálculo de disponibilidad filtra por estas dos columnas en
            // cada listado del catálogo.
            $table->index(['estado', 'expira_en']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
