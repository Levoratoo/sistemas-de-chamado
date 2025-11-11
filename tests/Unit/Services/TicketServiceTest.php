<?php

namespace Tests\Unit\Services;

use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_work_on_ticket_with_correct_area(): void
    {
        $area = Area::create(['name' => 'TI']);
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'login' => 'test',
            'password' => bcrypt('password'),
        ]);
        $user->areas()->attach($area->id);
        
        $role = Role::where('name', 'atendente')->first();
        $user->role_id = $role->id;
        $user->save();

        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-001',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'area_id' => $area->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $user->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = TicketService::canWorkOnTicket($user, $ticket);

        $this->assertTrue($result);
    }

    public function test_cannot_work_on_ticket_without_area(): void
    {
        $area = Area::create(['name' => 'TI']);
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'login' => 'test2',
            'password' => bcrypt('password'),
        ]);
        // Não anexar área ao usuário
        
        $role = Role::where('name', 'atendente')->first();
        $user->role_id = $role->id;
        $user->save();

        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-002',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'area_id' => $area->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $user->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = TicketService::canWorkOnTicket($user, $ticket);

        $this->assertFalse($result);
    }

    public function test_admin_can_work_on_any_ticket(): void
    {
        $area = Area::create(['name' => 'TI']);
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admintest@example.com',
            'login' => 'admintest',
            'password' => bcrypt('password'),
        ]);
        
        $role = Role::where('name', 'admin')->first();
        $admin->role_id = $role->id;
        $admin->save();

        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-003',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'area_id' => $area->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $admin->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = TicketService::canWorkOnTicket($admin, $ticket);

        $this->assertTrue($result);
    }

    public function test_assigned_user_can_work_on_ticket(): void
    {
        $user = User::create([
            'name' => 'Assigned User',
            'email' => 'assigned@example.com',
            'login' => 'assigned',
            'password' => bcrypt('password'),
        ]);
        
        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-004',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $user->id,
            'assignee_id' => $user->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = TicketService::canWorkOnTicket($user, $ticket);

        $this->assertTrue($result);
    }

    public function test_eligible_assignees_returns_correct_users(): void
    {
        $area = Area::create(['name' => 'TI']);
        
        $attendant = User::create([
            'name' => 'Atendente',
            'email' => 'atendente@example.com',
            'login' => 'atendente',
            'password' => bcrypt('password'),
        ]);
        $attendant->areas()->attach($area->id);
        $role = Role::where('name', 'atendente')->first();
        $attendant->role_id = $role->id;
        $attendant->save();

        $user = User::create([
            'name' => 'Usuário',
            'email' => 'usuario@example.com',
            'login' => 'usuario',
            'password' => bcrypt('password'),
        ]);
        $role = Role::where('name', 'usuario')->first();
        $user->role_id = $role->id;
        $user->save();

        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-005',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'area_id' => $area->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $user->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);
        $admin = User::where('email', 'admin@local')->first();

        $eligible = TicketService::eligibleAssignees($ticket, $admin);

        $this->assertGreaterThan(0, $eligible->count());
        $this->assertTrue($eligible->contains($attendant));
        $this->assertTrue($eligible->contains($admin));
    }
}

