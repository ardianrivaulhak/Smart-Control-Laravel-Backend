<?php

use App\Models\CpiOrder;
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
        Schema::create('cpi_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stream_id');
            $table->uuid('user_id');
            $table->uuid('document_id');
            $table->enum('status', [CpiOrder::STATUS_WAITING, CpiOrder::STATUS_APPROVED, CpiOrder::STATUS_DECLINED]);
            $table->integer('rev');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cpi_orders');
    }
};
