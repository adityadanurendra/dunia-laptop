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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->restrictOnDelete();
            $table->string('courier_code', 30)->index();
            $table->string('courier_service', 50);
            $table->string('tracking_number', 100)->nullable()->index();
            $table->decimal('shipping_cost', 15, 2)->unsigned()->default(0);
            $table->string('status', 30)->default('pending')->index();
            $table->string('recipient_name', 150);
            $table->string('recipient_phone', 25);
            $table->text('destination_address');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};

