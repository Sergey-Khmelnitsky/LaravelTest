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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 255);
            $table->foreignId('cuisine_id')->constrained('cuisines')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->integer('prep_time')->nullable()->comment('Время подготовки в минутах');
            $table->integer('cook_time')->nullable()->comment('Время приготовления в минутах');
            $table->integer('servings')->nullable()->comment('Количество порций');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
