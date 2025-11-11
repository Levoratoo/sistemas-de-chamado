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
        Schema::create('sla_request_types', function (Blueprint $table) {
            $table->id();
            $table->string('request_type'); // reembolso, adiantamento, rh, contabilidade, etc.
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->integer('response_time_minutes'); // Tempo para primeira resposta
            $table->integer('resolve_time_minutes'); // Tempo para resolução
            $table->boolean('active')->default(true);
            $table->text('description')->nullable(); // Descrição do SLA
            $table->timestamps();
            
            // Índice único para request_type + priority
            $table->unique(['request_type', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_request_types');
    }
};