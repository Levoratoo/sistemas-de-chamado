<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Models\Sla;
use App\Models\SlaRequestType;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlaCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SlaCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SlaCalculationService();
        $this->seed();
    }

    public function test_calculates_sla_for_ticket_by_request_type(): void
    {
        // Criar SLA para tipo de chamado específico
        $slaRequestType = SlaRequestType::create([
            'request_type' => 'equipment',
            'priority' => 'high',
            'response_time_minutes' => 60,
            'resolve_time_minutes' => 240,
            'active' => true,
        ]);

        $result = $this->service->calculateSlaForTicket('equipment', 'high');

        $this->assertEquals('request_type', $result['source']);
        $this->assertEquals(60, $result['response_time_minutes']);
        $this->assertEquals(240, $result['resolve_time_minutes']);
        $this->assertNotNull($result['due_at']);
        $this->assertNotNull($result['respond_by']);
    }

    public function test_falls_back_to_category_sla_when_request_type_not_found(): void
    {
        $category = Category::create(['name' => 'TI', 'active' => true]);
        
        $sla = Sla::create([
            'category_id' => $category->id,
            'priority' => 'medium',
            'response_time_minutes' => 120,
            'resolve_time_minutes' => 480,
            'name' => 'SLA Teste',
            'active' => true,
        ]);

        $result = $this->service->calculateSlaForTicket('unknown_type', 'medium', $category->id);

        $this->assertEquals('category', $result['source']);
        $this->assertEquals(120, $result['response_time_minutes']);
        $this->assertEquals(480, $result['resolve_time_minutes']);
    }

    public function test_uses_default_sla_when_no_specific_sla_found(): void
    {
        $result = $this->service->calculateSlaForTicket('unknown_type', 'medium', null);

        $this->assertEquals('default', $result['source']);
        $this->assertNotNull($result['due_at']);
        $this->assertNotNull($result['respond_by']);
    }

    public function test_check_sla_compliance_detects_overdue_tickets(): void
    {
        $user = User::first();
        $category = Category::first();
        
        $ticket = Ticket::create([
            'code' => 'TST-001',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'priority' => 'medium',
            'status' => Ticket::STATUS_IN_PROGRESS,
            'requester_id' => $user->id,
            'due_at' => now()->subHours(2),
            'respond_by' => now()->subHours(3),
            'last_status_change_at' => now(),
        ]);

        $compliance = $this->service->checkSlaCompliance($ticket);

        $this->assertTrue($compliance['is_overdue']);
        $this->assertEquals('overdue', $compliance['sla_status']);
        $this->assertGreaterThan(0, $compliance['overdue_hours']);
    }

    public function test_check_sla_compliance_detects_near_due_tickets(): void
    {
        $user = User::first();
        $category = Category::first();
        
        $ticket = Ticket::create([
            'code' => 'TST-002',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'priority' => 'medium',
            'status' => Ticket::STATUS_IN_PROGRESS,
            'requester_id' => $user->id,
            'due_at' => now()->addHour(),
            'respond_by' => now()->addMinutes(30),
            'last_status_change_at' => now(),
        ]);

        $compliance = $this->service->checkSlaCompliance($ticket);

        $this->assertFalse($compliance['is_overdue']);
        $this->assertTrue($compliance['is_near_due']);
        $this->assertEquals('warning', $compliance['sla_status']);
    }

    public function test_check_sla_compliance_detects_on_time_tickets(): void
    {
        $user = User::first();
        $category = Category::first();
        
        $ticket = Ticket::create([
            'code' => 'TST-003',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'priority' => 'medium',
            'status' => Ticket::STATUS_IN_PROGRESS,
            'requester_id' => $user->id,
            'due_at' => now()->addDays(2),
            'respond_by' => now()->addDay(),
            'last_status_change_at' => now(),
        ]);

        $compliance = $this->service->checkSlaCompliance($ticket);

        $this->assertFalse($compliance['is_overdue']);
        $this->assertFalse($compliance['is_near_due']);
        $this->assertEquals('on_time', $compliance['sla_status']);
    }

    public function test_apply_sla_to_ticket_updates_dates(): void
    {
        $user = User::first();
        $category = Category::first();
        
        $ticket = Ticket::create([
            'code' => 'TST-004',
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'category_id' => $category->id,
            'priority' => 'high',
            'status' => Ticket::STATUS_OPEN,
            'requester_id' => $user->id,
            'request_type' => 'equipment',
            'due_at' => now()->addDay(),
            'respond_by' => now()->addHours(12),
            'last_status_change_at' => now(),
        ]);

        $slaRequestType = SlaRequestType::create([
            'request_type' => 'equipment',
            'priority' => 'high',
            'response_time_minutes' => 60,
            'resolve_time_minutes' => 240,
            'active' => true,
        ]);

        $updatedTicket = $this->service->applySlaToTicket($ticket);

        $this->assertNotNull($updatedTicket->due_at);
        $this->assertNotNull($updatedTicket->respond_by);
        $this->assertTrue($updatedTicket->due_at->isAfter(now()));
        $this->assertTrue($updatedTicket->respond_by->isAfter(now()));
    }
}

