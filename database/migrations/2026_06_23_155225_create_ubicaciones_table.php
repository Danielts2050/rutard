<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ruta_id')->constrained()->cascadeOnDelete();
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->float('velocidad', 5, 1)->default(0);
            $table->dateTime('fecha_hora');

            $table->index(['ruta_id', 'fecha_hora']);
            $table->index('fecha_hora');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones');
    }
};
