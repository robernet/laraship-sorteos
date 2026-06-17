<?php

namespace Corals\Modules\Sorteos\database\migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SorteosTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sorteos_sorteos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('prize_description')->nullable();
            $table->decimal('ticket_price', 10, 2)->default(0);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->date('draw_date')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_public')->default(false);

            $this->commonColumns($table);
        });

        Schema::create('sorteos_carteras', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sorteo_id');
            $table->string('code', 20);
            $table->unsignedInteger('physical_start');
            $table->unsignedInteger('physical_end');
            $table->unsignedInteger('digital_start');
            $table->unsignedInteger('digital_end');
            $table->string('status')->default('available');

            $table->foreign('sorteo_id')->references('id')->on('sorteos_sorteos')->onDelete('cascade');
            $table->unique(['sorteo_id', 'code']);

            $this->commonColumns($table);
        });

        Schema::create('sorteos_boletos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sorteo_id');
            $table->unsignedInteger('cartera_id');
            $table->unsignedInteger('physical_number');
            $table->unsignedInteger('digital_number');
            $table->string('status')->default('available');

            $table->foreign('sorteo_id')->references('id')->on('sorteos_sorteos')->onDelete('cascade');
            $table->foreign('cartera_id')->references('id')->on('sorteos_carteras')->onDelete('cascade');
            $table->unique(['sorteo_id', 'digital_number']);

            $this->commonColumns($table);
        });

        Schema::create('sorteos_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sorteo_id');
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('buyer_phone', 50);
            $table->string('payment_method', 50);
            $table->string('status', 20)->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('payment_reference', 60)->nullable();

            $table->foreign('sorteo_id')->references('id')->on('sorteos_sorteos')->onDelete('cascade');

            $this->commonColumns($table);
        });

        Schema::create('sorteos_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('boleto_id');
            $table->decimal('price', 10, 2)->default(0);

            $table->foreign('order_id')->references('id')->on('sorteos_orders')->onDelete('cascade');
            $table->foreign('boleto_id')->references('id')->on('sorteos_boletos')->onDelete('cascade');
            $table->unique(['order_id', 'boleto_id']);

            $table->text('properties')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sorteos_order_items');
        Schema::dropIfExists('sorteos_orders');
        Schema::dropIfExists('sorteos_boletos');
        Schema::dropIfExists('sorteos_carteras');
        Schema::dropIfExists('sorteos_sorteos');
    }

    protected function commonColumns(Blueprint $table)
    {
        $table->text('properties')->nullable();
        $table->auditable();
        $table->softDeletes();
        $table->timestamps();
    }
}
