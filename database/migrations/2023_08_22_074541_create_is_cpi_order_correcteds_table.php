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
        Schema::create('is_cpi_order_correcteds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cpi_order_before_id');
            $table->uuid('cpi_order_after_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('is_cpi_order_correcteds');
    }
};