<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reacomoda los datos que ya existen al modelo nuevo.
 *
 * Cada factura vieja se convierte en un Pedido:
 *   - pagada = true  -> Pedido 'pagado'   + se conserva la Factura, apuntando a él
 *   - pagada = false -> Pedido 'vencido'  + se BORRA la factura
 *
 * Ese segundo caso es el punto de todo el cambio: nunca fueron facturas, eran
 * pedidos abandonados. Se conservan como pedidos vencidos para no perder el
 * historial, pero dejan de figurar como comprobantes de venta.
 *
 * Los renglones de detalle_factura pasan a pedido_items y la tabla se elimina.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->foreignId('pedido_id')->nullable()->after('id')
                ->constrained('pedidos')->onDelete('cascade');
        });

        foreach (DB::table('facturas')->orderBy('id')->get() as $factura) {
            $estabaPagada = (bool) $factura->pagada;

            $pedidoId = DB::table('pedidos')->insertGetId([
                'user_id' => $factura->user_id,
                'estado' => $estabaPagada ? 'pagado' : 'vencido',
                'metodo_entrega' => $factura->metodo_entrega ?? 'envio',
                'envio' => $factura->envio ?? 0,
                'precio_total' => $factura->precio_total,
                'expira_en' => null, // ninguno de los dos estados reserva stock
                'created_at' => $factura->created_at,
                'updated_at' => $factura->updated_at,
            ]);

            $renglones = DB::table('detalle_factura')
                ->where('factura_id', $factura->id)
                ->get();

            foreach ($renglones as $renglon) {
                DB::table('pedido_items')->insert([
                    'pedido_id' => $pedidoId,
                    'cerveza_id' => $renglon->cerveza_id,
                    'cantidad' => $renglon->cantidad,
                    'precio_unitario' => $renglon->precio_unitario,
                    // subtotal se agregó después de crear la tabla, puede venir null
                    'subtotal' => $renglon->subtotal ?? ($renglon->precio_unitario * $renglon->cantidad),
                    'created_at' => $renglon->created_at,
                    'updated_at' => $renglon->updated_at,
                ]);
            }

            if ($estabaPagada) {
                DB::table('facturas')->where('id', $factura->id)->update(['pedido_id' => $pedidoId]);
            } else {
                // Nunca fue una venta: el pedido vencido ya guarda el historial
                DB::table('facturas')->where('id', $factura->id)->delete();
            }
        }

        Schema::dropIfExists('detalle_factura');

        Schema::table('facturas', function (Blueprint $table) {
            // Ahora que la factura existe solo si hubo pago, `pagada` sobra.
            // metodo_entrega y envio son propiedades del pedido, no del comprobante.
            $table->dropColumn(['pagada', 'metodo_entrega', 'envio']);
        });
    }

    public function down(): void
    {
        Schema::create('detalle_factura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained()->onDelete('cascade');
            $table->foreignId('cerveza_id')->constrained()->onDelete('cascade');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 8, 2);
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::table('facturas', function (Blueprint $table) {
            $table->boolean('pagada')->default(false);
            $table->string('metodo_entrega')->default('envio');
            $table->decimal('envio', 10, 2)->default(0);
        });

        foreach (DB::table('facturas')->whereNotNull('pedido_id')->get() as $factura) {
            $pedido = DB::table('pedidos')->find($factura->pedido_id);

            DB::table('facturas')->where('id', $factura->id)->update([
                'pagada' => true,
                'metodo_entrega' => $pedido->metodo_entrega,
                'envio' => $pedido->envio,
            ]);

            foreach (DB::table('pedido_items')->where('pedido_id', $pedido->id)->get() as $item) {
                DB::table('detalle_factura')->insert([
                    'factura_id' => $factura->id,
                    'cerveza_id' => $item->cerveza_id,
                    'cantidad' => $item->cantidad,
                    'precio_unitario' => $item->precio_unitario,
                    'subtotal' => $item->subtotal,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]);
            }
        }

        Schema::table('facturas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pedido_id');
        });
    }
};
