<?php

namespace Tests\Unit\Policies;

use App\Models\Area;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected TicketPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TicketPolicy();
        $this->seed();
    }

    public function test_admin_can_view_all_tickets(): void
    {
        $admin = User::where('email', 'admin@local')->first();
        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-POL-003',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $admin->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = $this->policy->view($admin, $ticket);

        $this->assertTrue($result);
    }

    public function test_user_can_view_own_tickets(): void
    {
        $user = User::where('email', 'usuario@local')->first();
        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-POL-004',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $user->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = $this->policy->view($user, $ticket);

        $this->assertTrue($result);
    }

    public function test_user_cannot_view_other_users_tickets(): void
    {
        $user = User::where('email', 'usuario@local')->first();
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'login' => 'other',
            'password' => bcrypt('password'),
        ]);
        
        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-POL-005',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $otherUser->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = $this->policy->view($user, $ticket);

        $this->assertFalse($result);
    }

    public function test_assigned_user_can_view_ticket(): void
    {
        $user = User::where('email', 'atendente@local')->first();
        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-POL-006',
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

        $result = $this->policy->view($user, $ticket);

        $this->assertTrue($result);
    }

    public function test_atendente_can_view_tickets_from_their_areas(): void
    {
        $area = Area::create(['name' => 'TI']);
        $attendant = User::where('email', 'atendente@local')->first();
        $attendant->areas()->attach($area->id);

        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-POL-001',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'area_id' => $area->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $attendant->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = $this->policy->view($attendant, $ticket);

        $this->assertTrue($result);
    }

    public function test_atendente_cannot_view_tickets_from_other_areas(): void
    {
        $area1 = Area::create(['name' => 'TI']);
        $area2 = Area::create(['name' => 'RH']);
        
        $attendant = User::where('email', 'atendente@local')->first();
        $attendant->areas()->attach($area1->id);

        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-POL-002',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'area_id' => $area2->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $attendant->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = $this->policy->view($attendant, $ticket);

        $this->assertFalse($result);
    }

    public function test_admin_can_work_on_any_ticket(): void
    {
        $admin = User::where('email', 'admin@local')->first();
        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-POL-007',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $admin->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = $this->policy->work($admin, $ticket);

        $this->assertTrue($result);
    }

    public function test_atendente_can_work_on_unassigned_ticket_in_their_area(): void
    {
        $area = Area::create(['name' => 'TI']);
        $attendant = User::where('email', 'atendente@local')->first();
        $attendant->areas()->attach($area->id);

        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-POL-008',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'area_id' => $area->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $attendant->id,
            'assignee_id' => null,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = $this->policy->work($attendant, $ticket);

        $this->assertTrue($result);
    }

    public function test_atendente_cannot_work_on_assigned_ticket(): void
    {
        $area = Area::create(['name' => 'TI']);
        $attendant = User::where('email', 'atendente@local')->first();
        $attendant->areas()->attach($area->id);
        
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other2@example.com',
            'login' => 'other2',
            'password' => bcrypt('password'),
        ]);
        
        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-POL-009',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'area_id' => $area->id,
            'priority' => 'medium',
            'status' => 'open',
            'requester_id' => $attendant->id,
            'assignee_id' => $otherUser->id,
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $result = $this->policy->work($attendant, $ticket);

        $this->assertFalse($result);
    }

    public function test_assigned_user_can_work_on_ticket(): void
    {
        $user = User::where('email', 'atendente@local')->first();
        $category = \App\Models\Category::first();
        $ticket = Ticket::create([
            'code' => 'TEST-POL-010',
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

        $result = $this->policy->work($user, $ticket);

        $this->assertTrue($result);
    }
}

