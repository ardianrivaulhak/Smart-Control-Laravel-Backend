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
        Schema::create('log_trail_declineds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cpi_order_id');
            $table->uuid('declined_reason_id');
            $table->string('change');
            $table->string('inspector');
            $table->string('declined_by');
            $table->date('timestamp');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_trail_declineds');
    }
};
