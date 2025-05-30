<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_fermentacions', function (Blueprint $table) {
            $table->string('levadura')->after('descripcion');
            $table->string('temperatura')->after('levadura');   // ej. "15‑22 °C"
            $table->string('tiempo')->after('temperatura');     // ej. "3‑7 días"
        });
    }

    public function down(): void
    {
        Schema::table('tipo_fermentacions', function (Blueprint $table) {
            $table->dropColumn(['levadura', 'temperatura', 'tiempo']);
        });
    }
};