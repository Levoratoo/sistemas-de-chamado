<?php

namespace App\Http\Controllers;

use App\Helpers\Sanitizer;
use App\Http\Requests\TicketStoreRequest;
use App\Models\Area;
use App\Models\Category;
use App\Models\Ticket;
use App\Services\SlaCalculationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();
        $isGestor = $user->isGestor();
        $isAtendente = $user->isAtendente();
        $areaIdsFromProfiles = $user->groupsAreasIds();

        $query = Ticket::query()
            ->with(['category', 'area', 'requester', 'assignee'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('request_type')) {
            $requestType = $request->input('request_type');
            if ($requestType === 'geral') {
                $query->where(function($q) {
                    $q->whereNull('request_type')->orWhere('request_type', '');
                });
            } else {
                $query->where('request_type', $requestType);
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->boolean('assigned_to_me')) {
            $query->where('assignee_id', $user->id);
        }

        if ($request->boolean('my_tickets')) {
            $query->where('requester_id', $request->user()->id);
        }

        if ($request->filled('area')) {
            $areaName = $request->input('area');
            $query->whereHas('area', function($q) use ($areaName) {
                $q->where('name', 'like', '%' . $areaName . '%');
            });
        }

        if ($request->filled('area_id')) {
            $areaId = $request->input('area_id');
            $query->where('area_id', $areaId);
        }

        if ($request->filled('sla')) {
            $slaFilter = $request->input('sla');
            if ($slaFilter === 'overdue') {
                // Ticket vencido apenas se passaram mais de 7 dias desde a criação
                $sevenDaysAgo = now()->subDays(7);
                $query->where('status', '!=', Ticket::STATUS_FINALIZED)
                    ->where('created_at', '<', $sevenDaysAgo);
            } elseif ($slaFilter === 'warning') {
                $query->where('due_at', '>', now())
                    ->where('due_at', '<=', now()->addHours(2))
                    ->where('status', '!=', Ticket::STATUS_FINALIZED);
            }
        }

        if (! $isAdmin) {
            if ($isGestor) {
                $ids = !empty($areaIdsFromProfiles) ? $areaIdsFromProfiles : [-1];
                $query->where(function ($inner) use ($user, $ids) {
                    $inner->whereIn('area_id', $ids)
                        ->orWhere('assignee_id', $user->id)
                        ->orWhere('requester_id', $user->id);
                });
            } elseif ($isAtendente) {
                $query->where(function ($inner) use ($user) {
                    $inner->where('assignee_id', $user->id)
                        ->orWhere('requester_id', $user->id);
                });
            } else {
                $query->where('requester_id', $user->id);
            }
        }

        $tickets = $query->paginate(15)->withQueryString();
        $categories = Category::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('tickets.index', compact('tickets', 'categories'));
    }

    public function create(Request $request): View
    {
        $areas = Area::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $categories = Category::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Buscar informações do tipo de solicitação se fornecido
        $requestType = null;
        if ($request->has('type')) {
            $requestType = \App\Models\RequestType::where('slug', $request->input('type'))->first();
        }

        return view('tickets.create', compact('areas', 'categories', 'requestType'));
    }

    public function createReimbursement(): View
    {
        return view('tickets.create-reimbursement');
    }

    public function createAdvance(): View
    {
        return view('tickets.create-advance');
    }

    public function createGeneralPayment(): View
    {
        return view('tickets.create-general-payment');
    }

    public function createCustomerReturn(): View
    {
        return view('tickets.create-customer-return');
    }

    public function createImportPayment(): View
    {
        return view('tickets.create-import-payment');
    }

    public function createRh(): View
    {
        return view('tickets.create-rh');
    }

    public function createAccounting(): View
    {
        return view('tickets.create-accounting');
    }

    public function storeReimbursement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'cost_center' => 'required|string|max:255',
            'approver_id' => 'nullable|exists:users,id',
            'description' => 'required|string',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_type' => 'required|in:transferencia,pix,boleto',
            'bank_data' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = $request->user();
        
        // Buscar área de Financeiro
        $financeArea = Area::where('name', 'Financeiro')->orWhere('name', 'LIKE', '%financeiro%')->first();
        
        if (!$financeArea) {
            return redirect()->back()->with('error', 'Área Financeiro não encontrada.');
        }

        // Buscar categoria padrão ou criar
        $category = Category::query()->where('active', true)->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => 'Financeiro',
                'description' => 'Categoria padrão para solicitações financeiras.',
                'active' => true,
            ]);
        }

        $now = now();

        $ticket = Ticket::create([
            'code' => $this->generateCode(),
            'title' => Sanitizer::sanitize($validated['title']),
            'description' => Sanitizer::sanitizeWithFormatting($validated['description'] ?? ''),
            'area_id' => $financeArea->id,
            'category_id' => $category->id,
            'requester_id' => $user->id,
            'status' => 'open',
            'priority' => 'high',
            'request_type' => 'reembolso',
            'company' => Sanitizer::sanitize($validated['company']),
            'cost_center' => Sanitizer::sanitize($validated['cost_center']),
            'approver_id' => $validated['approver_id'] ?? null,
            'payment_amount' => $validated['payment_amount'] ?? null,
            'payment_date' => $validated['payment_date'] ?? null,
            'payment_type' => $validated['payment_type'],
            'bank_data' => Sanitizer::sanitizeWithFormatting($validated['bank_data'] ?? ''),
            'respond_by' => $now,
            'due_at' => $now->copy()->addDays(7),
            'last_status_change_at' => $now,
        ]);

        // Processar anexos
        foreach ($request->file('attachments', []) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedName = Str::uuid()->toString() . '_' . $originalName;

            $path = Storage::disk('local')->putFileAs(
                'tickets/' . $ticket->id,
                $file,
                $storedName
            );

            $ticket->attachments()->create([
                'user_id' => $user->id,
                'filename' => $originalName,
                'path' => $path,
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }

        TicketEventService::log(
            $ticket,
            $user,
            'created',
            null,
            'open',
            [
                'request_type' => 'reembolso',
                'company' => $ticket->company,
                'payment_type' => $ticket->payment_type,
            ]
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de reembolso criada com sucesso!');
    }

    public function storeAdvance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'opened_on_behalf_of' => 'nullable|exists:users,id',
            'company' => 'required|string|max:255',
            'cost_center' => 'required|string|max:255',
            'approver_id' => 'nullable|exists:users,id',
            'description' => 'required|string',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_type' => 'required|in:transferencia,pix,boleto',
            'bank_data' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = $request->user();
        
        // Buscar área de Financeiro
        $financeArea = Area::where('name', 'Financeiro')->orWhere('name', 'LIKE', '%financeiro%')->first();
        
        if (!$financeArea) {
            return redirect()->back()->with('error', 'Área Financeiro não encontrada.');
        }

        // Buscar categoria padrão ou criar
        $category = Category::query()->where('active', true)->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => 'Financeiro',
                'description' => 'Categoria padrão para solicitações financeiras.',
                'active' => true,
            ]);
        }

        $now = now();

        $ticket = Ticket::create([
            'code' => $this->generateCode(),
            'title' => Sanitizer::sanitize($validated['title']),
            'description' => Sanitizer::sanitizeWithFormatting($validated['description'] ?? ''),
            'area_id' => $financeArea->id,
            'category_id' => $category->id,
            'requester_id' => $user->id,
            'opened_on_behalf_of' => $validated['opened_on_behalf_of'] ?? null,
            'status' => 'open',
            'priority' => 'high',
            'request_type' => 'adiantamento',
            'company' => Sanitizer::sanitize($validated['company']),
            'cost_center' => Sanitizer::sanitize($validated['cost_center']),
            'approver_id' => $validated['approver_id'] ?? null,
            'payment_amount' => $validated['payment_amount'] ?? null,
            'payment_date' => $validated['payment_date'] ?? null,
            'payment_type' => $validated['payment_type'],
            'bank_data' => Sanitizer::sanitizeWithFormatting($validated['bank_data'] ?? ''),
            'respond_by' => $now,
            'due_at' => $now->copy()->addDays(7),
            'last_status_change_at' => $now,
        ]);

        // Processar anexos
        foreach ($request->file('attachments', []) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedName = Str::uuid()->toString() . '_' . $originalName;

            $path = Storage::disk('local')->putFileAs(
                'tickets/' . $ticket->id,
                $file,
                $storedName
            );

            $ticket->attachments()->create([
                'user_id' => $user->id,
                'filename' => $originalName,
                'path' => $path,
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }

        TicketEventService::log(
            $ticket,
            $user,
            'created',
            null,
            'open',
            [
                'request_type' => 'adiantamento',
                'company' => $ticket->company,
                'payment_type' => $ticket->payment_type,
            ]
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de adiantamento criada com sucesso!');
    }

    public function storeGeneralPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'cost_center' => 'required|string|max:255',
            'approver_id' => 'nullable|exists:users,id',
            'description' => 'required|string',
            'payment_type' => 'required|in:transferencia,pix,boleto',
            'bank_data' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = $request->user();
        
        // Buscar área de Financeiro
        $financeArea = Area::where('name', 'Financeiro')->orWhere('name', 'LIKE', '%financeiro%')->first();
        
        if (!$financeArea) {
            return redirect()->back()->with('error', 'Área Financeiro não encontrada.');
        }

        // Buscar categoria padrão ou criar
        $category = Category::query()->where('active', true)->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => 'Financeiro',
                'description' => 'Categoria padrão para solicitações financeiras.',
                'active' => true,
            ]);
        }

        $now = now();

        $ticket = Ticket::create([
            'code' => $this->generateCode(),
            'title' => Sanitizer::sanitize($validated['title']),
            'description' => Sanitizer::sanitizeWithFormatting($validated['description'] ?? ''),
            'area_id' => $financeArea->id,
            'category_id' => $category->id,
            'requester_id' => $user->id,
            'status' => 'open',
            'priority' => 'high',
            'request_type' => 'pagamento_geral',
            'company' => Sanitizer::sanitize($validated['company']),
            'cost_center' => Sanitizer::sanitize($validated['cost_center']),
            'approver_id' => $validated['approver_id'] ?? null,
            'payment_type' => $validated['payment_type'],
            'bank_data' => Sanitizer::sanitizeWithFormatting($validated['bank_data'] ?? ''),
            'respond_by' => $now,
            'due_at' => $now->copy()->addDays(7),
            'last_status_change_at' => $now,
        ]);

        // Processar anexos
        foreach ($request->file('attachments', []) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedName = Str::uuid()->toString() . '_' . $originalName;

            $path = Storage::disk('local')->putFileAs(
                'tickets/' . $ticket->id,
                $file,
                $storedName
            );

            $ticket->attachments()->create([
                'user_id' => $user->id,
                'filename' => $originalName,
                'path' => $path,
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }

        TicketEventService::log(
            $ticket,
            $user,
            'created',
            null,
            'open',
            [
                'request_type' => 'pagamento_geral',
                'company' => $ticket->company,
                'payment_type' => $ticket->payment_type,
            ]
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de pagamento geral criada com sucesso!');
    }

    public function storeCustomerReturn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'cost_center' => 'required|string|max:255',
            'approver_id' => 'nullable|exists:users,id',
            'description' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = $request->user();
        
        $financeArea = Area::where('name', 'Financeiro')->orWhere('name', 'LIKE', '%financeiro%')->first();
        
        if (!$financeArea) {
            return redirect()->back()->with('error', 'Área Financeiro não encontrada.');
        }

        $category = Category::query()->where('active', true)->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => 'Financeiro',
                'description' => 'Categoria padrão para solicitações financeiras.',
                'active' => true,
            ]);
        }

        $now = now();

        $ticket = Ticket::create([
            'code' => $this->generateCode(),
            'title' => Sanitizer::sanitize($validated['title']),
            'description' => Sanitizer::sanitizeWithFormatting($validated['description'] ?? ''),
            'area_id' => $financeArea->id,
            'category_id' => $category->id,
            'requester_id' => $user->id,
            'status' => 'open',
            'priority' => 'high',
            'request_type' => 'devolucao_clientes',
            'company' => Sanitizer::sanitize($validated['company']),
            'cost_center' => Sanitizer::sanitize($validated['cost_center']),
            'approver_id' => $validated['approver_id'] ?? null,
            'respond_by' => $now,
            'due_at' => $now->copy()->addDays(7),
            'last_status_change_at' => $now,
        ]);

        foreach ($request->file('attachments', []) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedName = Str::uuid()->toString() . '_' . $originalName;

            $path = Storage::disk('local')->putFileAs(
                'tickets/' . $ticket->id,
                $file,
                $storedName
            );

            $ticket->attachments()->create([
                'user_id' => $user->id,
                'filename' => $originalName,
                'path' => $path,
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }

        TicketEventService::log(
            $ticket,
            $user,
            'created',
            null,
            'open',
            [
                'request_type' => 'devolucao_clientes',
                'company' => $ticket->company,
            ]
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de devolução de clientes criada com sucesso!');
    }

    public function storeImportPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'cost_center' => 'required|string|max:255',
            'approver_id' => 'nullable|exists:users,id',
            'description' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = $request->user();
        
        $financeArea = Area::where('name', 'Financeiro')->orWhere('name', 'LIKE', '%financeiro%')->first();
        
        if (!$financeArea) {
            return redirect()->back()->with('error', 'Área Financeiro não encontrada.');
        }

        $category = Category::query()->where('active', true)->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => 'Financeiro',
                'description' => 'Categoria padrão para solicitações financeiras.',
                'active' => true,
            ]);
        }

        $now = now();

        $ticket = Ticket::create([
            'code' => $this->generateCode(),
            'title' => Sanitizer::sanitize($validated['title']),
            'description' => Sanitizer::sanitizeWithFormatting($validated['description'] ?? ''),
            'area_id' => $financeArea->id,
            'category_id' => $category->id,
            'requester_id' => $user->id,
            'status' => 'open',
            'priority' => 'high',
            'request_type' => 'pagamento_importacoes',
            'company' => Sanitizer::sanitize($validated['company']),
            'cost_center' => Sanitizer::sanitize($validated['cost_center']),
            'approver_id' => $validated['approver_id'] ?? null,
            'respond_by' => $now,
            'due_at' => $now->copy()->addDays(7),
            'last_status_change_at' => $now,
        ]);

        foreach ($request->file('attachments', []) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedName = Str::uuid()->toString() . '_' . $originalName;

            $path = Storage::disk('local')->putFileAs(
                'tickets/' . $ticket->id,
                $file,
                $storedName
            );

            $ticket->attachments()->create([
                'user_id' => $user->id,
                'filename' => $originalName,
                'path' => $path,
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }

        TicketEventService::log(
            $ticket,
            $user,
            'created',
            null,
            'open',
            [
                'request_type' => 'pagamento_importacoes',
                'company' => $ticket->company,
            ]
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de pagamento de importações criada com sucesso!');
    }

    public function storeRh(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company' => 'required|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_amount' => 'nullable|numeric|min:0',
            'employee_code' => 'nullable|string|max:100',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = $request->user();
        
        $financeArea = Area::where('name', 'Financeiro')->orWhere('name', 'LIKE', '%financeiro%')->first();
        
        if (!$financeArea) {
            return redirect()->back()->with('error', 'Área Financeiro não encontrada.');
        }

        $category = Category::query()->where('active', true)->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => 'Financeiro',
                'description' => 'Categoria padrão para solicitações financeiras.',
                'active' => true,
            ]);
        }

        $now = now();

        $ticket = Ticket::create([
            'code' => $this->generateCode(),
            'title' => Sanitizer::sanitize($validated['title']),
            'description' => Sanitizer::sanitizeWithFormatting($validated['description'] ?? ''),
            'area_id' => $financeArea->id,
            'category_id' => $category->id,
            'requester_id' => $user->id,
            'status' => 'open',
            'priority' => 'high',
            'request_type' => 'rh',
            'company' => Sanitizer::sanitize($validated['company']),
            'payment_date' => $validated['payment_date'] ?? null,
            'payment_amount' => $validated['payment_amount'] ?? null,
            'employee_code' => Sanitizer::sanitize($validated['employee_code'] ?? ''),
            'respond_by' => $now,
            'due_at' => $now->copy()->addDays(7),
            'last_status_change_at' => $now,
        ]);

        foreach ($request->file('attachments', []) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedName = Str::uuid()->toString() . '_' . $originalName;

            $path = Storage::disk('local')->putFileAs(
                'tickets/' . $ticket->id,
                $file,
                $storedName
            );

            $ticket->attachments()->create([
                'user_id' => $user->id,
                'filename' => $originalName,
                'path' => $path,
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }

        TicketEventService::log(
            $ticket,
            $user,
            'created',
            null,
            'open',
            [
                'request_type' => 'rh',
                'company' => $ticket->company,
                'employee_code' => $ticket->employee_code,
            ]
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de RH criada com sucesso!');
    }

    public function storeAccounting(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'company' => 'required|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_amount' => 'nullable|numeric|min:0',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $user = $request->user();
        
        $financeArea = Area::where('name', 'Financeiro')->orWhere('name', 'LIKE', '%financeiro%')->first();
        
        if (!$financeArea) {
            return redirect()->back()->with('error', 'Área Financeiro não encontrada.');
        }

        $category = Category::query()->where('active', true)->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => 'Financeiro',
                'description' => 'Categoria padrão para solicitações financeiras.',
                'active' => true,
            ]);
        }

        $now = now();

        $ticket = Ticket::create([
            'code' => $this->generateCode(),
            'title' => Sanitizer::sanitize($validated['title']),
            'description' => Sanitizer::sanitizeWithFormatting($validated['description'] ?? ''),
            'area_id' => $financeArea->id,
            'category_id' => $category->id,
            'requester_id' => $user->id,
            'status' => 'open',
            'priority' => 'high',
            'request_type' => 'contabilidade',
            'company' => Sanitizer::sanitize($validated['company']),
            'payment_date' => $validated['payment_date'] ?? null,
            'payment_amount' => $validated['payment_amount'] ?? null,
            'respond_by' => $now,
            'due_at' => $now->copy()->addDays(7),
            'last_status_change_at' => $now,
        ]);

        foreach ($request->file('attachments', []) as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedName = Str::uuid()->toString() . '_' . $originalName;

            $path = Storage::disk('local')->putFileAs(
                'tickets/' . $ticket->id,
                $file,
                $storedName
            );

            $ticket->attachments()->create([
                'user_id' => $user->id,
                'filename' => $originalName,
                'path' => $path,
                'mime' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }

        TicketEventService::log(
            $ticket,
            $user,
            'created',
            null,
            'open',
            [
                'request_type' => 'contabilidade',
                'company' => $ticket->company,
            ]
        );

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Solicitação de contabilidade criada com sucesso!');
    }

    public function store(TicketStoreRequest $request): RedirectResponse
    {
        $user = $request->user();

        $ticket = DB::transaction(function () use ($request, $user) {
            // Usar a categoria selecionada pelo usuário ou buscar a primeira disponível
            $categoryId = $request->input('category_id');
            
            if (!$categoryId) {
                $categoryId = Category::query()->where('active', true)->value('id');
            }

            // Fallback: se ainda não tiver categoria, criar uma padrão
            if (!$categoryId) {
                $categoryId = Category::create([
                    'name' => 'Geral',
                    'description' => 'Categoria padrao gerada automaticamente.',
                    'active' => true,
                ])->id;
            }

            $now = now();

            // Calcular SLA baseado no tipo de chamado e prioridade
            $slaService = app(SlaCalculationService::class);
            $requestType = $request->input('request_type', 'geral');
            $priority = $request->input('priority', 'medium');
            
            $slaData = $slaService->calculateSlaForTicket($requestType, $priority, $categoryId);

            $ticket = Ticket::create([
                'code' => $this->generateCode(),
                'title' => Sanitizer::sanitize($request->input('title')),
                'description' => Sanitizer::sanitizeWithFormatting($request->input('description') ?? ''),
                'area_id' => $request->input('area_id'),
                'requester_id' => $user->id,
                'status' => 'open',
                'category_id' => $categoryId,
                'priority' => $priority,
                'request_type' => $requestType,
                'respond_by' => $slaData['respond_by'],
                'due_at' => $slaData['due_at'],
                'last_status_change_at' => $now,
            ]);

            foreach ($request->file('attachments', []) as $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                $originalName = $file->getClientOriginalName();
                $storedName = Str::uuid()->toString() . '_' . $originalName;

                $path = Storage::disk('local')->putFileAs(
                    'tickets/' . $ticket->id,
                    $file,
                    $storedName
                );

                $ticket->attachments()->create([
                    'user_id' => $user->id,
                    'filename' => $originalName,
                    'path' => $path,
                    'mime' => $file->getMimeType() ?? 'application/octet-stream',
                    'size' => $file->getSize(),
                ]);
            }

            TicketEventService::log($ticket, $user, 'created', null, $ticket->status);

            return $ticket;
        });

        return redirect()
            ->route('tickets.create')
            ->with('success', "Chamado aberto: {$ticket->code}");
    }

    public function show(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        // Recarregar o ticket do banco SEM cache para garantir dados atualizados
        $ticket = Ticket::withoutGlobalScopes()
            ->with([
                'area',
                'requester',
                'openedOnBehalfOf',
                'assignee',
                'approver',
                'resolver',
                'attachments',
                'comments.user',
            ])
            ->findOrFail($ticket->id);
        
        // Forçar refresh dos relacionamentos
        $ticket->loadMissing([
            'area',
            'requester',
            'openedOnBehalfOf',
            'assignee',
            'approver',
            'resolver',
        ]);

        $events = $ticket->events()
            ->with('user')
            ->orderBy('occurred_at')
            ->get();

        return view('tickets.show', [
            'ticket' => $ticket,
            'events' => $events,
        ]);
    }

    public function edit(Ticket $ticket)
    {
        abort(404);
    }

    public function update(Request $request, Ticket $ticket)
    {
        abort(404);
    }

    public function destroy(Ticket $ticket)
    {
        abort(404);
    }

    private function generateCode(): string
    {
        $year = (int) now()->format('Y');

        $lastCode = Ticket::whereYear('created_at', $year)
            ->where('code', 'like', "CH-{$year}-%")
            ->orderByDesc('id')
            ->value('code');

        $sequence = 1;

        if ($lastCode && preg_match('/^CH-\d{4}-(\d{6})$/', $lastCode, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        do {
            $code = sprintf('CH-%d-%06d', $year, $sequence);
            $sequence++;
        } while (Ticket::where('code', $code)->exists());

        return $code;
    }
}

