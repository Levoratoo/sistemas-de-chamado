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
        Schema::table('tickets', function (Blueprint $table) {
            // Tipo de solicitação
            $table->string('request_type')->nullable()->after('code');
            
            // Campos específicos para Reembolso
            $table->string('company')->nullable()->after('description');
            $table->string('cost_center')->nullable()->after('company');
            $table->foreignId('approver_id')->nullable()->after('cost_center')->constrained('users')->nullOnDelete();
            $table->decimal('payment_amount', 15, 2)->nullable()->after('approver_id');
            $table->date('payment_date')->nullable()->after('payment_amount');
            $table->enum('payment_type', ['transferencia', 'pix', 'boleto'])->nullable()->after('payment_date');
            $table->text('bank_data')->nullable()->after('payment_type');
            
            // Índice para busca por tipo
            $table->index('request_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['request_type']);
            
            $table->dropConstrainedForeignId('approver_id');
            $table->dropColumn([
                'request_type',
                'company',
                'cost_center',
                'payment_amount',
                'payment_date',
                'payment_type',
                'bank_data',
            ]);
        });
    }
};











