<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sorteos_orders', function (Blueprint $table) {
            $table->unsignedInteger('asignado_id')->nullable()->after('sorteo_id');
            $table->foreign('asignado_id')->references('id')->on('sorteos_carteras_asignadas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sorteos_orders', function (Blueprint $table) {
            $table->dropForeign(['asignado_id']);
            $table->dropColumn('asignado_id');
        });
    }
};
