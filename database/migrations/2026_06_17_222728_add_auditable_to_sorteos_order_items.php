<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sorteos_order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->index()->after('properties');
            $table->unsignedBigInteger('updated_by')->nullable()->index()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('sorteos_order_items', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};
