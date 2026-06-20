<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sorteos_carteras', function (Blueprint $table) {
            $table->renameColumn('asignado_id', 'colaborador_id');
        });
    }

    public function down(): void
    {
        Schema::table('sorteos_carteras', function (Blueprint $table) {
            $table->renameColumn('colaborador_id', 'asignado_id');
        });
    }
};
