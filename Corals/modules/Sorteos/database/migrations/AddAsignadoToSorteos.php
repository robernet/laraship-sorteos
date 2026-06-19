<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAsignadoToSorteos extends Migration
{
    public function up()
    {
        Schema::create('sorteos_carteras_asignadas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('type', 20)->default('persona');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('properties')->nullable();
            $table->auditable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('sorteos_carteras', function (Blueprint $table) {
            $table->unsignedInteger('asignado_id')->nullable()->after('sorteo_id');
            $table->foreign('asignado_id')->references('id')->on('sorteos_carteras_asignadas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('sorteos_carteras', function (Blueprint $table) {
            $table->dropForeign(['asignado_id']);
            $table->dropColumn('asignado_id');
        });
        Schema::dropIfExists('sorteos_carteras_asignadas');
    }
}
