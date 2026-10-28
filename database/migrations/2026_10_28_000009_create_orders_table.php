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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->decimal('subtotal', 15, 2)->unsigned();
            $table->decimal('shipping_cost', 15, 2)->unsigned()->default(0);
            $table->decimal('discount_amount', 15, 2)->unsigned()->default(0);
            $table->decimal('total_amount', 15, 2)->unsigned()->index();
            $table->string('status', 30)->default('pending')->index();
            $table->text('customer_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

