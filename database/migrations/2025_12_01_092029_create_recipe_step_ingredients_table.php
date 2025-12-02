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
        Schema::create('recipe_step_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_step_id')->constrained('recipe_steps')->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('restrict');
            $table->decimal('amount', 8, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_step_ingredients');
    }
};
