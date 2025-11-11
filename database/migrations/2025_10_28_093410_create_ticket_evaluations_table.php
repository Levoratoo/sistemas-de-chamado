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
        Schema::create('ticket_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Quem avaliou
            $table->integer('rating')->unsigned(); // 1-5 estrelas
            $table->text('comment')->nullable(); // Comentário opcional
            $table->timestamp('evaluated_at');
            $table->timestamps();
            
            // Evitar avaliações duplicadas
            $table->unique(['ticket_id', 'user_id']);
            
            // Índices para performance
            $table->index(['ticket_id', 'rating']);
            $table->index('evaluated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_evaluations');
    }
};