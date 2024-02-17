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
        Schema::create('laundry_transaction_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundry_transaction_id')->constrained();
            $table->foreignId('laundry_base_rate_id')->nullable()->constrained();
            $table->foreignId('laundry_special_service_id')->nullable()->constrained();
            $table->float('qty');
            $table->float('rate');
            $table->float('special_service_charge')->nullable();
            $table->float('sub_total');
            $table->integer('duration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_transaction_lines');
    }
};
