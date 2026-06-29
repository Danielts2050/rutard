<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rutas', function (Blueprint $table) {
            if (!Schema::hasColumn('rutas', 'km_recorridos')) {
                $table->decimal('km_recorridos', 8, 2)->nullable()->after('duracion_minutos');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rutas', function (Blueprint $table) {
            $table->dropColumn('km_recorridos');
        });
    }
};
