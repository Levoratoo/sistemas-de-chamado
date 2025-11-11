<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_areas', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: "Financeiro", "TI", "Compras"
            $table->string('slug')->unique(); // Ex: "financeiro", "ti", "compras"
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Classe do ícone ou nome do ícone
            $table->string('color')->default('#3B82F6'); // Cor do tema da área
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_areas');
    }
};