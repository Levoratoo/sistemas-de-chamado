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
            if (!Schema::hasColumn('tickets', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assignee_id');
            }

            if (!Schema::hasColumn('tickets', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('assigned_at');
            }

            if (!Schema::hasColumn('tickets', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable()->after('started_at');
            }

            if (!Schema::hasColumn('tickets', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('respond_by');
            }

            if (!Schema::hasColumn('tickets', 'last_status_change_at')) {
                $table->timestamp('last_status_change_at')->nullable()->after('resolved_at');
            }

            if (!Schema::hasColumn('tickets', 'resolution_summary')) {
                $table->text('resolution_summary')->nullable()->after('last_status_change_at');
            }

            if (!Schema::hasColumn('tickets', 'resolution_by')) {
                $table->foreignId('resolution_by')
                    ->nullable()
                    ->after('resolution_summary')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $columns = [
                'assigned_at',
                'started_at',
                'first_response_at',
                'resolved_at',
                'last_status_change_at',
                'resolution_summary',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tickets', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('tickets', 'resolution_by')) {
                $table->dropConstrainedForeignId('resolution_by');
            }
        });
    }
};

