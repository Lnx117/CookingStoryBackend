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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name_ru', 255);
            $table->string('slug', 255)->nullable();
            $table->integer('calories')->nullable();   // ккал на 100 г
            $table->decimal('proteins', 5, 2)->nullable();
            $table->decimal('fats', 5, 2)->nullable();
            $table->decimal('carbs', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
