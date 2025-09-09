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
        Schema::table('recipes', function (Blueprint $table) {
            $table->decimal('proteins_total', 8, 2)->nullable()->after('calories_total');
            $table->decimal('fats_total', 8, 2)->nullable()->after('proteins_total');
            $table->decimal('carbs_total', 8, 2)->nullable()->after('fats_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['proteins_total', 'fats_total', 'carbs_total']);
        });
    }
};
