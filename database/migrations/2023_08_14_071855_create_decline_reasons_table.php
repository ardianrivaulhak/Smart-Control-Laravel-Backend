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
        Schema::create('decline_reasons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('section_approval_id')->nullable();
            $table->uuid('verification_approval_id')->nullable();
            $table->string('reason');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decline_reasons');
    }
};
