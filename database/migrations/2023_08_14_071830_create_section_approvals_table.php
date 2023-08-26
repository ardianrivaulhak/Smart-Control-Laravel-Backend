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
        Schema::create('section_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cpi_order_id');
            $table->uuid('stream_section_head_id');
            $table->enum('status', ['waiting', 'approved', 'declined'])->default('waiting');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_approvals');
    }
};
