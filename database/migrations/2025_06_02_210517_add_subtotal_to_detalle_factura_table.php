<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detalle_factura', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->after('precio_unitario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_factura', function (Blueprint $table) {
            $table->dropColumn('subtotal');
        });
    }
};
