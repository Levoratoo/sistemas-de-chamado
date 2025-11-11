<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Area;
use App\Services\SlaCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TicketApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::with(['area', 'category', 'requester', 'assignee', 'attachments', 'comments']);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        if ($request->filled('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }

        if ($request->filled('requester_id')) {
            $query->where('requester_id', $request->requester_id);
        }

        // Paginação
        $perPage = $request->get('per_page', 15);
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $tickets->items(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load(['area', 'category', 'requester', 'assignee', 'attachments', 'comments.user']);

        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }

    public function store(Request $request, SlaCalculationService $slaService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'area_id' => 'required|exists:areas,id',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'required|in:low,medium,high,critical',
            'request_type' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $validated = $validator->validated();

        // Calcular SLA
        $requestType = $validated['request_type'] ?? 'geral';
        $priority = $validated['priority'];
        $slaData = $slaService->calculateSlaForTicket($requestType, $priority, $validated['category_id']);

        $ticket = Ticket::create([
            'code' => $this->generateCode(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'area_id' => $validated['area_id'],
            'category_id' => $validated['category_id'],
            'requester_id' => $user->id,
            'status' => 'open',
            'priority' => $priority,
            'request_type' => $requestType,
            'respond_by' => $slaData['respond_by'],
            'due_at' => $slaData['due_at'],
            'last_status_change_at' => now(),
        ]);

        // Processar anexos
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('tickets/' . $ticket->id);
                    
                    $ticket->attachments()->create([
                        'user_id' => $user->id,
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
        }

        $ticket->load(['area', 'category', 'requester', 'assignee']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket criado com sucesso!',
            'data' => $ticket,
        ], 201);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:open,in_progress,accepted,waiting_user,finalized',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'assignee_id' => 'sometimes|nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        
        // Atualizar campos permitidos
        $ticket->update($validated);

        $ticket->load(['area', 'category', 'requester', 'assignee']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket atualizado com sucesso!',
            'data' => $ticket,
        ]);
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket excluído com sucesso!',
        ]);
    }

    public function assign(Request $request, Ticket $ticket): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'assignee_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $assignee = User::find($request->assignee_id);
        $ticket->update([
            'assignee_id' => $assignee->id,
            'assigned_at' => now(),
            'status' => 'in_progress',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket atribuído com sucesso!',
            'data' => $ticket->load(['assignee']),
        ]);
    }

    public function comment(Request $request, Ticket $ticket): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $comment = $ticket->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $request->comment,
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentário adicionado com sucesso!',
            'data' => $comment->load('user'),
        ], 201);
    }

    public function metrics(Request $request, SlaCalculationService $slaService): JsonResponse
    {
        $startDate = $request->get('start_date', now()->subDays(30));
        $endDate = $request->get('end_date', now());

        $slaStats = $slaService->getSlaStatistics($startDate, $endDate);

        $metrics = [
            'total_tickets' => Ticket::whereBetween('created_at', [$startDate, $endDate])->count(),
            'resolved_tickets' => Ticket::whereBetween('resolved_at', [$startDate, $endDate])->count(),
            'sla_compliance' => $slaStats['sla_compliance_rate'],
            'overdue_tickets' => $slaStats['overdue_tickets'],
            'near_due_tickets' => $slaStats['near_due_tickets'],
            'avg_resolution_time' => $this->getAvgResolutionTime($startDate, $endDate),
        ];

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    public function areas(): JsonResponse
    {
        $areas = Area::where('active', true)->get();

        return response()->json([
            'success' => true,
            'data' => $areas,
        ]);
    }

    public function users(): JsonResponse
    {
        $users = User::with('role')->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    private function generateCode(): string
    {
        return 'TK' . str_pad(Ticket::count() + 1, 6, '0', STR_PAD_LEFT);
    }

    private function getAvgResolutionTime($startDate, $endDate): float
    {
        $avgHours = Ticket::whereBetween('resolved_at', [$startDate, $endDate])
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        return round($avgHours ?? 0, 1);
    }
}