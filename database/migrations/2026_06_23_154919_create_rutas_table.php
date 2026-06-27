<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chofer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehiculo_id')->constrained('vehicles')->cascadeOnDelete();
            $table->dateTime('hora_inicio');
            $table->decimal('latitud_inicio', 10, 7);
            $table->decimal('longitud_inicio', 10, 7);
            $table->dateTime('hora_fin')->nullable();
            $table->decimal('latitud_fin', 10, 7)->nullable();
            $table->decimal('longitud_fin', 10, 7)->nullable();
            $table->unsignedInteger('duracion_minutos')->nullable();
            $table->enum('estado', ['activa', 'finalizada'])->default('activa');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
