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
        Schema::create('cpi_order_has_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cpi_order_id');
            $table->uuid('section_id')->nullable();
            $table->uuid('line_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpi_order_has_sections');
    }
};