<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baja lógica de una cuenta.
 *
 * A un cliente no se lo borra: sus pedidos y facturas son comprobantes de
 * ventas reales y tienen que sobrevivir. Además `pedidos.user_id` y
 * `facturas.user_id` están con onDelete cascade, así que un DELETE se llevaría
 * puesta la historia de esas ventas. Bloquear resuelve el caso sin perder nada:
 * la cuenta queda, pero no puede volver a entrar.
 *
 * Es timestamp y no booleano porque el "cuándo" se necesita en pantalla y sale
 * gratis: un boolean respondería lo mismo pero sin decir desde cuándo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('bloqueado_en')->nullable()->after('super_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bloqueado_en');
        });
    }
};
