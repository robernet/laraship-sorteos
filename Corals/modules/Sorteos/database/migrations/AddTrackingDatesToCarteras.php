<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sorteos_carteras', function (Blueprint $table) {
            $table->timestamp('asignado_at')->nullable()->after('asignado_id');
            $table->timestamp('entregado_at')->nullable()->after('asignado_at');
        });
    }

    public function down(): void
    {
        Schema::table('sorteos_carteras', function (Blueprint $table) {
            $table->dropColumn(['asignado_at', 'entregado_at']);
        });
    }
};
