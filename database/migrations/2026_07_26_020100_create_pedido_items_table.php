<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Renglones del pedido, con el precio congelado al momento de pedir.
 *
 * Reemplazan a detalle_factura: como el pedido es inmutable una vez creado, la
 * factura puede apuntar a él en vez de duplicar los renglones. Una sola tabla
 * de ítems, sin dos copias que se puedan desincronizar.
 *
 * restrictOnDelete en cerveza_id a propósito: con cascade, borrar una cerveza
 * del panel admin se llevaba puestos los renglones de compras históricas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedido_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('cerveza_id')->constrained('cervezas')->restrictOnDelete();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 8, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();

            // Para sumar lo reservado por cerveza sin escanear la tabla entera
            $table->index('cerveza_id');
            $table->unique(['pedido_id', 'cerveza_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_items');
    }
};
