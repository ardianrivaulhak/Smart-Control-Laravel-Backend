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
        Schema::create('problems', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('part_name')->nullable();
            $table->string('type_name')->nullable();
            $table->string('name')->nullable();
            $table->integer('lot')->nullable();
            $table->string('reason')->nullable();
            $table->string('action')->nullable();
            $table->integer('reject')->nullable();
            $table->integer('ng')->nullable();
            $table->integer('ok')->nullable();
            $table->integer('identity')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('problems');
    }
};
