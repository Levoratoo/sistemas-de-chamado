<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\Sla;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar dados básicos
        $this->seed();
    }

    public function test_user_can_create_ticket()
    {
        $user = User::where('email', 'usuario@local')->first();
        $category = Category::first();
        
        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Teste de chamado',
            'description' => 'Descrição do teste',
            'category_id' => $category->id,
            'priority' => 'medium',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'title' => 'Teste de chamado',
            'requester_id' => $user->id,
        ]);
    }

    public function test_ticket_gets_unique_code()
    {
        $user = User::where('email', 'usuario@local')->first();
        $category = Category::first();
        
        $this->actingAs($user)->post('/tickets', [
            'title' => 'Teste de chamado',
            'description' => 'Descrição do teste',
            'category_id' => $category->id,
            'priority' => 'medium',
        ]);

        $ticket = Ticket::first();
        $this->assertStringStartsWith('CH-', $ticket->code);
        $this->assertStringContainsString(date('Y'), $ticket->code);
    }

    public function test_sla_is_calculated_correctly()
    {
        $user = User::where('email', 'usuario@local')->first();
        $category = Category::first();
        $sla = Sla::where('category_id', $category->id)
                  ->where('priority', 'medium')
                  ->first();
        
        $this->actingAs($user)->post('/tickets', [
            'title' => 'Teste de chamado',
            'description' => 'Descrição do teste',
            'category_id' => $category->id,
            'priority' => 'medium',
        ]);

        $ticket = Ticket::first();
        $expectedRespondBy = $ticket->created_at->addMinutes($sla->response_time_minutes);
        $expectedDueAt = $ticket->created_at->addMinutes($sla->resolve_time_minutes);
        
        $this->assertEquals($expectedRespondBy->format('Y-m-d H:i'), $ticket->respond_by->format('Y-m-d H:i'));
        $this->assertEquals($expectedDueAt->format('Y-m-d H:i'), $ticket->due_at->format('Y-m-d H:i'));
    }

    public function test_atendente_can_update_ticket_status()
    {
        $atendente = User::where('email', 'atendente@local')->first();
        $ticket = Ticket::factory()->create();
        
        $response = $this->actingAs($atendente)->put("/tickets/{$ticket->id}", [
            'title' => $ticket->title,
            'description' => $ticket->description,
            'category_id' => $ticket->category_id,
            'priority' => $ticket->priority,
            'status' => 'in_progress',
            'assignee_id' => $atendente->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'assignee_id' => $atendente->id,
        ]);
    }

    public function test_user_can_add_comment()
    {
        $user = User::where('email', 'usuario@local')->first();
        $ticket = Ticket::factory()->create(['requester_id' => $user->id]);
        
        $response = $this->actingAs($user)->post("/tickets/{$ticket->id}/comments", [
            'body' => 'Comentário de teste',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => 'Comentário de teste',
            'is_internal' => false,
        ]);
    }

    public function test_atendente_can_add_internal_comment()
    {
        $atendente = User::where('email', 'atendente@local')->first();
        $ticket = Ticket::factory()->create();
        
        $response = $this->actingAs($atendente)->post("/tickets/{$ticket->id}/comments", [
            'body' => 'Nota interna',
            'is_internal' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $atendente->id,
            'body' => 'Nota interna',
            'is_internal' => true,
        ]);
    }

    public function test_sla_badge_shows_correct_status()
    {
        $ticket = Ticket::factory()->create([
            'due_at' => now()->subHour(), // Vencido
        ]);
        
        $this->assertEquals('overdue', $ticket->sla_status);
        $this->assertEquals('badge-danger', $ticket->sla_badge_class);
    }

    public function test_gestor_can_access_admin_panel()
    {
        $gestor = User::where('email', 'gestor@local')->first();
        
        $response = $this->actingAs($gestor)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_usuario_cannot_access_admin_panel()
    {
        $usuario = User::where('email', 'usuario@local')->first();
        
        $response = $this->actingAs($usuario)->get('/admin');
        $response->assertStatus(403);
    }
}











