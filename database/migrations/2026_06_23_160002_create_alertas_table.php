<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ruta_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tipo', 50);
            $table->string('titulo', 200);
            $table->text('mensaje');
            $table->json('metadata')->nullable();
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_alerta');
            $table->timestamps();

            $table->index(['user_id', 'leida']);
            $table->index(['tipo', 'created_at']);
            $table->index('fecha_alerta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
