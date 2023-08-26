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
        Schema::create('samplings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type_name')->nullable();
            $table->string('name')->nullable();
            $table->integer('std')->nullable();
            $table->integer('rh')->nullable();
            $table->integer('lh')->nullable();
            $table->boolean('judgement')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('samplings');
    }
};
