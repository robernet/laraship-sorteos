<?php

namespace Corals\Modules\ClubPago\database\migrations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ClubPagoTables extends Migration
{
    public function up(): void
    {
        Schema::create('clubpago_references', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id')->nullable()->index();
            $table->decimal('amount');
            $table->string('currency')->nullable();
            $table->string('reference')->nullable()->index();
            $table->string('authorization')->nullable();
            $table->string('bar_code')->nullable();
            $table->string('pay_format')->nullable();
            $table->text('message')->nullable();
            $table->string('folio')->nullable();
            $table->string('date')->nullable();
            $table->string('status');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('created_by')->nullable()->index();
            $table->unsignedInteger('updated_by')->nullable()->index();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('sorteos_orders')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubpago_references');
    }
}
