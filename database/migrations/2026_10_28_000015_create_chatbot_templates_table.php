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
        Schema::create('chatbot_templates', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->index();
            $table->text('trigger_keywords');
            $table->string('response_type', 30)->default('static')->index();
            $table->text('static_response')->nullable();
            $table->string('dynamic_handler', 100)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_templates');
    }
};

