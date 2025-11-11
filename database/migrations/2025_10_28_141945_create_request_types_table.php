<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_area_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Ex: "Solicitação de Reembolso", "Solicitação de Adiantamento"
            $table->string('slug')->unique(); // Ex: "reembolso", "adiantamento"
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Classe do ícone
            $table->string('color')->default('#3B82F6'); // Cor do ícone
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_types');
    }
};