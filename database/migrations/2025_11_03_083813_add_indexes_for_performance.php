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
        // Índices para tabela tickets
        Schema::table('tickets', function (Blueprint $table) {
            // Verificar se área_id existe antes de criar índices que dependem dela
            $hasAreaId = Schema::hasColumn('tickets', 'area_id');
            $hasAssignedAt = Schema::hasColumn('tickets', 'assigned_at');
            
            // Índice composto para filtros por status e área (usado em queries de atendentes)
            if ($hasAreaId && !$this->indexExists('tickets', 'tickets_status_area_id_index')) {
                $table->index(['status', 'area_id'], 'tickets_status_area_id_index');
            }
            
            // Índice composto para SLA (due_at + status)
            if (!$this->indexExists('tickets', 'tickets_due_at_status_index')) {
                $table->index(['due_at', 'status'], 'tickets_due_at_status_index');
            }
            
            // Índice composto para filtros por atribuição (assignee_id + assigned_at)
            if ($hasAssignedAt && !$this->indexExists('tickets', 'tickets_assignee_assigned_index')) {
                $table->index(['assignee_id', 'assigned_at'], 'tickets_assignee_assigned_index');
            }
            
            // Índice composto para filtros por solicitante e data (requester_id + created_at)
            if (!$this->indexExists('tickets', 'tickets_requester_created_index')) {
                $table->index(['requester_id', 'created_at'], 'tickets_requester_created_index');
            }
            
            // Índice simples para ordenação por data de criação
            if (!$this->indexExists('tickets', 'tickets_created_at_index')) {
                $table->index('created_at', 'tickets_created_at_index');
            }
            
            // Índice para área isolada (para queries que usam apenas area_id)
            if ($hasAreaId && !$this->indexExists('tickets', 'tickets_area_id_index')) {
                $table->index('area_id', 'tickets_area_id_index');
            }
        });

        // Índices para tabela ticket_events
        Schema::table('ticket_events', function (Blueprint $table) {
            // Índice composto para filtros por usuário e data (para histórico do usuário)
            if (!$this->indexExists('ticket_events', 'ticket_events_user_occurred_index')) {
                $table->index(['user_id', 'occurred_at'], 'ticket_events_user_occurred_index');
            }
            
            // Índice para tipo de evento (para análises por tipo)
            if (!$this->indexExists('ticket_events', 'ticket_events_type_index')) {
                $table->index('type', 'ticket_events_type_index');
            }
        });

        // Índices para tabela users
        Schema::table('users', function (Blueprint $table) {
            // Índice para role_id (já existe foreign key, mas índice pode ajudar em queries com JOIN)
            if (!$this->indexExists('users', 'users_role_id_index')) {
                $table->index('role_id', 'users_role_id_index');
            }
        });

        // Índices para tabela area_user (pivot)
        Schema::table('area_user', function (Blueprint $table) {
            // Índice para user_id (para queries que buscam áreas de um usuário)
            if (!$this->indexExists('area_user', 'area_user_user_id_index')) {
                $table->index('user_id', 'area_user_user_id_index');
            }
            
            // Índice para area_id (para queries que buscam usuários de uma área)
            if (!$this->indexExists('area_user', 'area_user_area_id_index')) {
                $table->index('area_id', 'area_user_area_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover índices da tabela tickets
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex('tickets_status_area_id_index');
            $table->dropIndex('tickets_due_at_status_index');
            $table->dropIndex('tickets_assignee_assigned_index');
            $table->dropIndex('tickets_requester_created_index');
            $table->dropIndex('tickets_created_at_index');
            $table->dropIndex('tickets_area_id_index');
        });

        // Remover índices da tabela ticket_events
        Schema::table('ticket_events', function (Blueprint $table) {
            $table->dropIndex('ticket_events_user_occurred_index');
            $table->dropIndex('ticket_events_type_index');
        });

        // Remover índices da tabela users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_id_index');
        });

        // Remover índices da tabela area_user
        Schema::table('area_user', function (Blueprint $table) {
            $table->dropIndex('area_user_user_id_index');
            $table->dropIndex('area_user_area_id_index');
        });
    }

    /**
     * Verificar se um índice já existe
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $connection = Schema::getConnection();
            $databaseName = $connection->getDatabaseName();
            
            $result = $connection->select(
                "SELECT COUNT(*) as count 
                 FROM information_schema.statistics 
                 WHERE table_schema = ? 
                 AND table_name = ? 
                 AND index_name = ?",
                [$databaseName, $table, $indexName]
            );
            
            return isset($result[0]) && $result[0]->count > 0;
        } catch (\Exception $e) {
            // Em caso de erro (ex: SQLite), assumir que o índice não existe
            return false;
        }
    }
};
