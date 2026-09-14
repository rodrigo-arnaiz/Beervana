<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Intentos de pago por Mercado Pago.
 *
 * Es una tabla aparte y no un par de columnas en pedidos porque un pedido puede
 * tener varios intentos: el cliente abandona el checkout, la tarjeta se rechaza,
 * vuelve a probar. Cada intento tiene su propia preferencia en MP y su propio
 * desenlace, y perder ese rastro deja sin explicación por qué un pedido figura
 * pagado o por qué alguien dice que pagó y el sistema no lo ve.
 *
 * También registra desde dónde se inició: la tienda (el cliente solo) o el
 * mostrador (el cajero generando el cobro), que es lo que después permite
 * atribuir la venta a quien atendió.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pedido_id')->constrained()->cascadeOnDelete();

            // Quién arrancó el cobro. Null si lo inició el cliente desde la
            // tienda; el cajero cuando sale del mostrador.
            $table->foreignId('iniciado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->string('origen', 20); // tienda | mostrador

            // Lo que devuelve MP al crear la preferencia
            $table->string('preference_id')->nullable()->index();

            // El id del pago concreto, que recién existe cuando alguien paga.
            // Una preferencia puede terminar en varios intentos de pago.
            $table->string('payment_id')->nullable()->index();

            $table->string('estado', 20)->default('pendiente');

            // Lo que MP informó tal cual, para poder explicar un rechazo
            $table->string('detalle_estado')->nullable();

            $table->decimal('monto', 10, 2);

            // La URL a la que hay que mandar al cliente (o poner en el QR)
            $table->text('init_point')->nullable();

            $table->timestamp('pagado_en')->nullable();

            $table->timestamps();

            // El mostrador consulta "el último intento de este pedido" en cada
            // vuelta del polling: conviene que no sea un scan.
            $table->index(['pedido_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
