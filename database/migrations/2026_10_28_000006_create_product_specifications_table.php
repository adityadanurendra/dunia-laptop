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
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('processor', 150)->index();
            $table->unsignedSmallInteger('ram_gb')->index();
            $table->string('ram_type', 50)->nullable();
            $table->string('storage_capacity', 50)->index();
            $table->string('storage_type', 50)->default('NVMe PCIe SSD');
            $table->string('gpu', 150)->index();
            $table->decimal('screen_size', 4, 1)->unsigned()->index();
            $table->string('screen_resolution', 60)->nullable();
            $table->string('physical_condition', 100)->nullable();
            $table->string('battery_condition', 100)->nullable();
            $table->string('operating_system', 100)->nullable();
            $table->string('warranty', 100)->nullable();
            $table->json('additional_specs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
    }
};

