<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TicketAttachmentController;
use App\Http\Controllers\TicketEvaluationController;
use App\Http\Controllers\TicketQueueController;
use App\Http\Controllers\TicketWorkController;
use App\Http\Controllers\TicketKanbanController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required'],
    ]);

    $login = strtolower(trim($credentials['login']));

    if (Auth::attempt(['login' => $login, 'password' => $credentials['password']], $request->boolean('remember'))) {
        $request->session()->regenerate();

        // Evitar redirecionar para endpoints internos (ex.: /api/*) capturados como "intended"
        // e garantir que o usuário caia na home após login.
        return redirect()->route('home');
    }

    return back()->withErrors([
        'login' => 'Credenciais invalidas.',
    ])->onlyInput('login');
})->middleware('throttle:5,1'); // Rate limiting simples temporário

Route::post('/logout', function (Request $request) {
    Auth::guard()->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');
// Redirecionar para dashboard se logado, senão para login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Perfil do Usuário
Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [App\Http\Controllers\ProfileController::class, 'index'])->name('index');
    Route::post('/photo', [App\Http\Controllers\ProfileController::class, 'updatePhoto'])->name('update-photo');
    Route::delete('/photo', [App\Http\Controllers\ProfileController::class, 'removePhoto'])->name('remove-photo');
});

// Dashboard
// Página inicial - visão geral limpa e focada
Route::get('/', [HomeController::class, 'index'])->middleware('auth')->name('home');

// Dashboard executivo - gráficos e métricas avançadas
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Route::get('/dashboard/executive', [App\Http\Controllers\ExecutiveDashboardController::class, 'index'])->middleware('auth')->name('dashboard.executive');

// Tickets
Route::middleware('auth')->group(function () {
    Route::get('/queue', [TicketQueueController::class, 'index'])->name('queue.index');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/kanban', [TicketKanbanController::class, 'index'])->name('tickets.kanban');
    Route::get('/tickets/create-type', function () {
        return view('tickets.create-type');
    })->name('tickets.create-type');
    
    // Nova estrutura hierárquica
    Route::get('/tickets/select-area', [App\Http\Controllers\RequestAreaController::class, 'index'])->name('request-areas.index');
    Route::get('/tickets/select-area/{area}', [App\Http\Controllers\RequestAreaController::class, 'show'])->name('request-areas.show');
    Route::get('/tickets/create-equipment', [App\Http\Controllers\TicketEquipmentController::class, 'create'])->name('tickets.create-equipment');
    Route::post('/tickets/store-equipment', [App\Http\Controllers\TicketEquipmentController::class, 'store'])->name('tickets.store-equipment')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-systems', [App\Http\Controllers\TicketSystemsController::class, 'create'])->name('tickets.create-systems');
    Route::post('/tickets/store-systems', [App\Http\Controllers\TicketSystemsController::class, 'store'])->name('tickets.store-systems')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-internet', [App\Http\Controllers\TicketInternetController::class, 'create'])->name('tickets.create-internet');
    Route::post('/tickets/store-internet', [App\Http\Controllers\TicketInternetController::class, 'store'])->name('tickets.store-internet')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-access', [App\Http\Controllers\TicketAccessController::class, 'create'])->name('tickets.create-access');
    Route::post('/tickets/store-access', [App\Http\Controllers\TicketAccessController::class, 'store'])->name('tickets.store-access')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-employee', [App\Http\Controllers\TicketEmployeeController::class, 'create'])->name('tickets.create-employee');
    Route::post('/tickets/store-employee', [App\Http\Controllers\TicketEmployeeController::class, 'store'])->name('tickets.store-employee')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-replacement', [App\Http\Controllers\TicketReplacementController::class, 'create'])->name('tickets.create-replacement');
    Route::post('/tickets/store-replacement', [App\Http\Controllers\TicketReplacementController::class, 'store'])->name('tickets.store-replacement')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-purchase', [App\Http\Controllers\TicketPurchaseController::class, 'create'])->name('tickets.create-purchase');
    Route::post('/tickets/store-purchase', [App\Http\Controllers\TicketPurchaseController::class, 'store'])->name('tickets.store-purchase')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-sample', [App\Http\Controllers\TicketSampleController::class, 'create'])->name('tickets.create-sample');
    Route::post('/tickets/store-sample', [App\Http\Controllers\TicketSampleController::class, 'store'])->name('tickets.store-sample')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-item', [App\Http\Controllers\TicketItemController::class, 'create'])->name('tickets.create-item');
    Route::post('/tickets/store-item', [App\Http\Controllers\TicketItemController::class, 'store'])->name('tickets.store-item')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-supplier', [App\Http\Controllers\TicketSupplierController::class, 'create'])->name('tickets.create-supplier');
    Route::post('/tickets/store-supplier', [App\Http\Controllers\TicketSupplierController::class, 'store'])->name('tickets.store-supplier')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-job-opening', [App\Http\Controllers\TicketJobOpeningController::class, 'create'])->name('tickets.create-job-opening');
    Route::post('/tickets/store-job-opening', [App\Http\Controllers\TicketJobOpeningController::class, 'store'])->name('tickets.store-job-opening')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-personnel-movement', [App\Http\Controllers\TicketPersonnelMovementController::class, 'create'])->name('tickets.create-personnel-movement');
    Route::post('/tickets/store-personnel-movement', [App\Http\Controllers\TicketPersonnelMovementController::class, 'store'])->name('tickets.store-personnel-movement')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-termination', [App\Http\Controllers\TicketTerminationController::class, 'create'])->name('tickets.create-termination');
    Route::post('/tickets/store-termination', [App\Http\Controllers\TicketTerminationController::class, 'store'])->name('tickets.store-termination')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-vacation', [App\Http\Controllers\TicketVacationController::class, 'create'])->name('tickets.create-vacation');
    Route::post('/tickets/store-vacation', [App\Http\Controllers\TicketVacationController::class, 'store'])->name('tickets.store-vacation')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-disciplinary', [App\Http\Controllers\TicketDisciplinaryController::class, 'create'])->name('tickets.create-disciplinary');
    Route::post('/tickets/store-disciplinary', [App\Http\Controllers\TicketDisciplinaryController::class, 'store'])->name('tickets.store-disciplinary')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-benefits', [App\Http\Controllers\TicketBenefitsController::class, 'create'])->name('tickets.create-benefits');
    Route::post('/tickets/store-benefits', [App\Http\Controllers\TicketBenefitsController::class, 'store'])->name('tickets.store-benefits')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-training', [App\Http\Controllers\TicketTrainingController::class, 'create'])->name('tickets.create-training');
    Route::post('/tickets/store-training', [App\Http\Controllers\TicketTrainingController::class, 'store'])->name('tickets.store-training')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-communication', [App\Http\Controllers\TicketCommunicationController::class, 'create'])->name('tickets.create-communication');
    Route::post('/tickets/store-communication', [App\Http\Controllers\TicketCommunicationController::class, 'store'])->name('tickets.store-communication')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-overtime', [App\Http\Controllers\TicketOvertimeController::class, 'create'])->name('tickets.create-overtime');
    Route::post('/tickets/store-overtime', [App\Http\Controllers\TicketOvertimeController::class, 'store'])->name('tickets.store-overtime')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-payroll', [App\Http\Controllers\TicketPayrollController::class, 'create'])->name('tickets.create-payroll');
    Route::post('/tickets/store-payroll', [App\Http\Controllers\TicketPayrollController::class, 'store'])->name('tickets.store-payroll')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-medical', [App\Http\Controllers\TicketMedicalController::class, 'create'])->name('tickets.create-medical');
    Route::post('/tickets/store-medical', [App\Http\Controllers\TicketMedicalController::class, 'store'])->name('tickets.store-medical')->middleware('throttle:10,1');
    
    Route::get('/tickets/create-rrl', [App\Http\Controllers\TicketRRLController::class, 'create'])->name('tickets.create-rrl');
    Route::post('/tickets/store-rrl', [App\Http\Controllers\TicketRRLController::class, 'store'])->name('tickets.store-rrl')->middleware('throttle:10,1');
    
    // RRI - Registro de Reclamação Interna
    Route::get('/tickets/create-rri', [App\Http\Controllers\TicketRRIController::class, 'create'])->name('tickets.create-rri');
    Route::post('/tickets/store-rri', [App\Http\Controllers\TicketRRIController::class, 'store'])->name('tickets.store-rri')->middleware('throttle:10,1');
    
    // RRQ - Registro de Reclamação de Qualidade
    Route::get('/tickets/create-rrq', [App\Http\Controllers\TicketRRQController::class, 'create'])->name('tickets.create-rrq');
    Route::post('/tickets/store-rrq', [App\Http\Controllers\TicketRRQController::class, 'store'])->name('tickets.store-rrq')->middleware('throttle:10,1');
    
    // Gabarito - Pré Impressão
    Route::get('/tickets/create-gabarito', [App\Http\Controllers\TicketGabaritoController::class, 'create'])->name('tickets.create-gabarito');
    Route::post('/tickets/store-gabarito', [App\Http\Controllers\TicketGabaritoController::class, 'store'])->name('tickets.store-gabarito')->middleware('throttle:10,1');
    
    // Layout - Pré Impressão
    Route::get('/tickets/create-layout', [App\Http\Controllers\TicketLayoutController::class, 'create'])->name('tickets.create-layout');
    Route::post('/tickets/store-layout', [App\Http\Controllers\TicketLayoutController::class, 'store'])->name('tickets.store-layout')->middleware('throttle:10,1');

    // Mock up - Pré Impressão
    Route::get('/tickets/create-mockup', [App\Http\Controllers\TicketMockupController::class, 'create'])->name('tickets.create-mockup');
    Route::post('/tickets/store-mockup', [App\Http\Controllers\TicketMockupController::class, 'store'])->name('tickets.store-mockup')->middleware('throttle:10,1');

    // Mock up Impresso - Pré Impressão
    Route::get('/tickets/create-mockup-impresso', [App\Http\Controllers\TicketMockupPrintedController::class, 'create'])->name('tickets.create-mockup-impresso');
    Route::post('/tickets/store-mockup-impresso', [App\Http\Controllers\TicketMockupPrintedController::class, 'store'])->name('tickets.store-mockup-impresso')->middleware('throttle:10,1');

    // Puxada de Cor - Pré Impressão
    Route::get('/tickets/create-puxada-cor', [App\Http\Controllers\TicketPuxadaCorController::class, 'create'])->name('tickets.create-puxada-cor');
    Route::post('/tickets/store-puxada-cor', [App\Http\Controllers\TicketPuxadaCorController::class, 'store'])->name('tickets.store-puxada-cor')->middleware('throttle:10,1');

    // 3D/Site - Pré Impressão
    Route::get('/tickets/create-3d-site', [App\Http\Controllers\TicketThreeDSiteController::class, 'create'])->name('tickets.create-3d-site');
    Route::post('/tickets/store-3d-site', [App\Http\Controllers\TicketThreeDSiteController::class, 'store'])->name('tickets.store-3d-site')->middleware('throttle:10,1');

    // Prova Contratual - Pré Impressão
    Route::get('/tickets/create-prova-contratual', [App\Http\Controllers\TicketProvaContratualController::class, 'create'])->name('tickets.create-prova-contratual');
    Route::post('/tickets/store-prova-contratual', [App\Http\Controllers\TicketProvaContratualController::class, 'store'])->name('tickets.store-prova-contratual')->middleware('throttle:10,1');

    // Impressão - Pré Impressão
    Route::get('/tickets/create-impressao', [App\Http\Controllers\TicketImpressaoController::class, 'create'])->name('tickets.create-impressao');
    Route::post('/tickets/store-impressao', [App\Http\Controllers\TicketImpressaoController::class, 'store'])->name('tickets.store-impressao')->middleware('throttle:10,1');
    
    // Desenvolvimento de Produto - Pré Impressão
    Route::get('/tickets/create-desenvol-produto', [App\Http\Controllers\TicketDesenvolvimentoProdutoController::class, 'create'])->name('tickets.create-desenvol-produto');
    Route::post('/tickets/store-desenvol-produto', [App\Http\Controllers\TicketDesenvolvimentoProdutoController::class, 'store'])->name('tickets.store-desenvol-produto')->middleware('throttle:10,1');
    
    // Rota genérica para tipos sem formulário específico
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::get('/tickets/create-reimbursement', [TicketController::class, 'createReimbursement'])->name('tickets.create-reimbursement');
    Route::get('/tickets/create-advance', [TicketController::class, 'createAdvance'])->name('tickets.create-advance');
    Route::get('/tickets/create-general-payment', [TicketController::class, 'createGeneralPayment'])->name('tickets.create-general-payment');
    Route::get('/tickets/create-customer-return', [TicketController::class, 'createCustomerReturn'])->name('tickets.create-customer-return');
    Route::get('/tickets/create-import-payment', [TicketController::class, 'createImportPayment'])->name('tickets.create-import-payment');
    Route::get('/tickets/create-rh', [TicketController::class, 'createRh'])->name('tickets.create-rh');
    Route::get('/tickets/create-accounting', [TicketController::class, 'createAccounting'])->name('tickets.create-accounting');
    
    // Rate limiting: 10 tickets por minuto por usuário
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store')->middleware('throttle:10,1');
    Route::post('/tickets/store-reimbursement', [TicketController::class, 'storeReimbursement'])->name('tickets.store-reimbursement')->middleware('throttle:10,1');
    Route::post('/tickets/store-advance', [TicketController::class, 'storeAdvance'])->name('tickets.store-advance')->middleware('throttle:10,1');
    Route::post('/tickets/store-general-payment', [TicketController::class, 'storeGeneralPayment'])->name('tickets.store-general-payment')->middleware('throttle:10,1');
    Route::post('/tickets/store-customer-return', [TicketController::class, 'storeCustomerReturn'])->name('tickets.store-customer-return')->middleware('throttle:10,1');
    Route::post('/tickets/store-import-payment', [TicketController::class, 'storeImportPayment'])->name('tickets.store-import-payment')->middleware('throttle:10,1');
    Route::post('/tickets/store-rh', [TicketController::class, 'storeRh'])->name('tickets.store-rh')->middleware('throttle:10,1');
    Route::post('/tickets/store-accounting', [TicketController::class, 'storeAccounting'])->name('tickets.store-accounting')->middleware('throttle:10,1');
    Route::post('/tickets/{ticket}/kanban/status', [TicketKanbanController::class, 'updateStatus'])->name('tickets.kanban.update-status')->middleware('throttle:30,1');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::get('/tickets/{ticket}/edit', [TicketController::class, 'edit'])->name('tickets.edit');
    Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy'])->name('tickets.destroy');

    // Rate limiting em ações de tickets: 20 por minuto
    Route::post('/tickets/{ticket}/assign', [TicketWorkController::class, 'assign'])->name('tickets.assign')->middleware('throttle:30,1');
    Route::post('/tickets/{ticket}/comment', [TicketWorkController::class, 'comment'])->name('tickets.comment')->middleware('throttle:50,1');
    Route::post('/tickets/{ticket}/await', [TicketWorkController::class, 'markWaiting'])->name('tickets.await')->middleware('throttle:20,1');
    Route::post('/tickets/{ticket}/finalize', [TicketWorkController::class, 'finalize'])->name('tickets.finalize')->middleware('throttle:20,1');
    Route::post('/tickets/{ticket}/delegate', [TicketWorkController::class, 'delegate'])->name('tickets.delegate')->middleware('throttle:20,1');
    Route::post('/tickets/{ticket}/return-to-queue', [TicketWorkController::class, 'returnToQueue'])->name('tickets.returnToQueue')->middleware('throttle:20,1');
    Route::post('/tickets/{ticket}/mark-analysis', [TicketWorkController::class, 'markAnalysis'])->name('tickets.markAnalysis')->middleware('throttle:10,1');
    Route::post('/tickets/{ticket}/mark-third-party', [TicketWorkController::class, 'markThirdParty'])->name('tickets.markThirdParty')->middleware('throttle:10,1');
    Route::post('/tickets/{ticket}/resume', [TicketWorkController::class, 'resume'])->name('tickets.resume')->middleware('throttle:10,1');
    Route::post('/tickets/{ticket}/cancel', [TicketWorkController::class, 'cancel'])->name('tickets.cancel')->middleware('throttle:10,1');

    Route::post('tickets/{ticket}/comments', [CommentController::class, 'store'])->name('tickets.comments.store')->middleware('throttle:20,1');
    
    // Rate limiting em uploads: 5 por minuto (prevenir spam de arquivos)
    Route::post('tickets/{ticket}/attachments', [AttachmentController::class, 'store'])->name('tickets.attachments.store')->middleware('throttle:5,1');
    Route::post('tickets/{ticket}/attachments-from-show', [TicketAttachmentController::class, 'storeFromShow'])->name('tickets.attachments.storeFromShow')->middleware('throttle:5,1');
    
    Route::get('tickets/{ticket}/attachments/{attachment}', [TicketAttachmentController::class, 'download'])->name('tickets.attachments.download');
    Route::delete('tickets/{ticket}/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('tickets.attachments.destroy');
    
    // Rotas de avaliação
    Route::get('/evaluations/pending', [TicketEvaluationController::class, 'pending'])->name('evaluations.pending');
    Route::get('/tickets/{ticket}/evaluate', [TicketEvaluationController::class, 'create'])->name('tickets.evaluate');
    Route::post('/tickets/{ticket}/evaluate', [TicketEvaluationController::class, 'store'])->name('tickets.evaluate.store')->middleware('throttle:5,1');
    
    // Rotas para dados dos gráficos
    Route::get('/api/charts/trend', [ChartController::class, 'trendData'])->name('charts.trend');
    Route::get('/api/charts/area-distribution', [ChartController::class, 'areaDistribution'])->name('charts.area-distribution');
    Route::get('/api/charts/sla-compliance', [ChartController::class, 'slaCompliance'])->name('charts.sla-compliance');
    Route::get('/api/charts/attendant-performance', [ChartController::class, 'attendantPerformance'])->name('charts.attendant-performance');
    Route::get('/api/charts/status-distribution', [ChartController::class, 'statusDistribution'])->name('charts.status-distribution');
    
    // Rotas de notificações
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/api/notifications/recent', [NotificationController::class, 'getRecent'])->name('notifications.recent');
});

// Admin routes (apenas para gestores e admins)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {

        return view('admin.index');
    })->name('index');

    Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);

    // Categories
    Route::get('/categories', function () {
        $categories = \App\Models\Category::withCount('tickets')->get();
        return view('admin.categories.index', compact('categories'));
    })->name('categories.index');
    
    Route::get('/categories/create', function () {
        return view('admin.categories.create');
    })->name('categories.create');
    
    Route::post('/categories', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
        ]);
        
        \App\Models\Category::create($request->all());
        return redirect()->route('admin.categories.index')->with('success', 'Categoria criada com sucesso!');
    })->name('categories.store');
    
    Route::get('/categories/{category}/edit', function (\App\Models\Category $category) {
        return view('admin.categories.edit', compact('category'));
    })->name('categories.edit');
    
    Route::put('/categories/{category}', function (\Illuminate\Http\Request $request, \App\Models\Category $category) {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
        ]);
        
        $category->update($request->all());
        return redirect()->route('admin.categories.index')->with('success', 'Categoria atualizada com sucesso!');
    })->name('categories.update');
    
    Route::delete('/categories/{category}', function (\App\Models\Category $category) {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Categoria excluída com sucesso!');
    })->name('categories.destroy');
    
    // SLAs
    Route::get('/slas', function () {
        $slas = \App\Models\Sla::with('category')->get();
        return view('admin.slas.index', compact('slas'));
    })->name('slas.index');
    
    Route::get('/slas/create', function () {
        $categories = \App\Models\Category::where('active', true)->get();
        return view('admin.slas.create', compact('categories'));
    })->name('slas.create');
    
    Route::post('/slas', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'required|in:low,medium,high,critical',
            'response_time_minutes' => 'required|integer|min:1',
            'resolve_time_minutes' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);
        
        \App\Models\Sla::create($request->all());
        return redirect()->route('admin.slas.index')->with('success', 'SLA criada com sucesso!');
    })->name('slas.store');
    
    Route::get('/slas/{sla}/edit', function (\App\Models\Sla $sla) {
        $categories = \App\Models\Category::where('active', true)->get();
        return view('admin.slas.edit', compact('sla', 'categories'));
    })->name('slas.edit');
    
    Route::put('/slas/{sla}', function (\Illuminate\Http\Request $request, \App\Models\Sla $sla) {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'priority' => 'required|in:low,medium,high,critical',
            'response_time_minutes' => 'required|integer|min:1',
            'resolve_time_minutes' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);
        
        $sla->update($request->all());
        return redirect()->route('admin.slas.index')->with('success', 'SLA atualizada com sucesso!');
    })->name('slas.update');
    
    Route::delete('/slas/{sla}', function (\App\Models\Sla $sla) {
        $sla->delete();
        return redirect()->route('admin.slas.index')->with('success', 'SLA excluída com sucesso!');
    })->name('slas.destroy');
    
    // Teams
    Route::get('/teams', function () {
        $teams = \App\Models\Team::withCount('users')->get();
        return view('admin.teams.index', compact('teams'));
    })->name('teams.index');
    
    Route::get('/teams/create', function () {
        return view('admin.teams.create');
    })->name('teams.create');
    
    Route::post('/teams', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        
        \App\Models\Team::create($request->all());
        return redirect()->route('admin.teams.index')->with('success', 'Equipe criada com sucesso!');
    })->name('teams.store');
    
    Route::get('/teams/{team}/edit', function (\App\Models\Team $team) {
        return view('admin.teams.edit', compact('team'));
    })->name('teams.edit');
    
    Route::put('/teams/{team}', function (\Illuminate\Http\Request $request, \App\Models\Team $team) {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);
        
        $team->update($request->all());
        return redirect()->route('admin.teams.index')->with('success', 'Equipe atualizada com sucesso!');
    })->name('teams.update');
    
    Route::delete('/teams/{team}', function (\App\Models\Team $team) {
        $team->delete();
        return redirect()->route('admin.teams.index')->with('success', 'Equipe excluída com sucesso!');
    })->name('teams.destroy');
    
    // Users
});
