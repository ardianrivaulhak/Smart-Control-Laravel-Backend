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
        Schema::create('access_permissions', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->uuid('role_id');
            $table->uuid('permission_id');
            $table->uuid('access_id');
            $table->boolean('status');
            $table->boolean('is_disable');
            $table->timestamps();
            $table->softDeletes();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_permissions');
    }
};
