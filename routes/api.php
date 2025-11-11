<?php

use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API Routes com autenticação Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // Tickets API (Nova API completa)
    Route::apiResource('tickets', TicketApiController::class);
    
    // Ações específicas de tickets
    Route::post('/tickets/{ticket}/assign', [TicketApiController::class, 'assign']);
    Route::post('/tickets/{ticket}/comment', [TicketApiController::class, 'comment']);
    
    // Métricas e dados auxiliares
    Route::get('/metrics', [TicketApiController::class, 'metrics']);
    Route::get('/areas', [TicketApiController::class, 'areas']);
    Route::get('/users', [TicketApiController::class, 'users']);
    
    // API Legacy (mantida para compatibilidade)
    Route::post('tickets/{ticket}/comments', [CommentController::class, 'store']);
    Route::post('tickets/{ticket}/attachments', [AttachmentController::class, 'store']);
    Route::get('tickets/{ticket}/attachments/{attachment}/download', [AttachmentController::class, 'download']);
    Route::delete('tickets/{ticket}/attachments/{attachment}', [AttachmentController::class, 'destroy']);
    
    // Endpoints para leitura de dados de apoio
    Route::get('categories', function () {
        return \App\Models\Category::where('active', true)->get();
    });
    
    Route::get('slas', function () {
        return \App\Models\Sla::with('category')->where('active', true)->get();
    });
    
    // Relatórios API
    Route::prefix('reports')->group(function () {
        Route::get('/', [App\Http\Controllers\ReportController::class, 'index']);
        Route::post('/generate', [App\Http\Controllers\ReportController::class, 'generate']);
        Route::post('/schedule', [App\Http\Controllers\ReportController::class, 'scheduleReport']);
    });
    
    // Dashboard Executivo API
    Route::get('/dashboard/executive', [App\Http\Controllers\ExecutiveDashboardController::class, 'index']);
});

// Rotas públicas da API (sem autenticação)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'sla_base' => '7 days',
    ]);
});

Route::get('/status', function () {
    return response()->json([
        'database' => 'connected',
        'cache' => 'working',
        'queue' => 'running',
        'sla_base' => '7 days',
    ]);
});

