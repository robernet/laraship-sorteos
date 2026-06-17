<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sorteos_orders', function (Blueprint $table) {
            $table->string('buyer_city', 100)->nullable()->after('buyer_phone');
            $table->string('buyer_state', 100)->nullable()->after('buyer_city');
        });
    }

    public function down(): void
    {
        Schema::table('sorteos_orders', function (Blueprint $table) {
            $table->dropColumn(['buyer_city', 'buyer_state']);
        });
    }
};
