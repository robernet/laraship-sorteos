<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('sorteos_carteras_asignadas', 'sorteos_colaboradores');
    }

    public function down(): void
    {
        Schema::rename('sorteos_colaboradores', 'sorteos_carteras_asignadas');
    }
};
