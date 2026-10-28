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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->string('name', 255)->index();
            $table->string('slug', 255)->unique();
            $table->string('sku', 60)->unique();
            $table->string('condition', 20)->default('new')->index();
            $table->decimal('price', 15, 2)->unsigned()->index();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('weight_grams')->default(1500);
            $table->longText('description')->nullable();
            $table->string('status', 20)->default('published')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

