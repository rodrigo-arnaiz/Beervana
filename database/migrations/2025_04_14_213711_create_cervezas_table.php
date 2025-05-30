<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cervezas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('precio', 8, 2);
            $table->foreignId('marca_id')->constrained('marcas')->onDelete('cascade');
            $table->decimal('graduacion', 5, 2);
            $table->string('tipo_envase');
            $table->foreignId('estilo_id')->constrained('estilos')->onDelete('cascade');
            $table->integer('ibu');
            $table->string('capacidad');
            $table->string('imagen', 80);
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cervezas');
    }
};
