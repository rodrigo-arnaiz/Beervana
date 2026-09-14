<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El QR presencial de un cobro.
 *
 * Un mismo intento de cobro puede tener dos puertas abiertas a la vez: el QR
 * (se paga desde la app de Mercado Pago) y el link del checkout (se paga con
 * tarjeta en el navegador). Son dos objetos distintos en MP para la misma
 * venta, y los dos vuelven etiquetados con el código del pedido, así que da
 * igual por cuál entre la plata.
 *
 * qr_data es la trama EMVCo: el string que se dibuja como código QR. No es una
 * URL y no sirve para abrir en el navegador.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->text('qr_data')->nullable()->after('init_point');
            $table->string('in_store_order_id')->nullable()->after('qr_data');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['qr_data', 'in_store_order_id']);
        });
    }
};
