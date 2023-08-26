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
        Schema::create('cpi_order_has_control_processes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cpi_order_id');
            $table->uuid('form_control_process_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpi_order_has_control_processes');
    }
};
