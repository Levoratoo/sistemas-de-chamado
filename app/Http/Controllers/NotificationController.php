<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->user()->notifications();

        // Filtros
        if ($request->filled('category')) {
            $category = $request->input('category');
            $query->whereJsonContains('data->category', $category);
        }

        if ($request->filled('priority')) {
            $priority = $request->input('priority');
            $query->whereJsonContains('data->priority', $priority);
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($request->input('status') === 'unread') {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    public function getUnreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }

    public function getRecent(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()->limit(10)->get();
        
        return response()->json(['notifications' => $notifications]);
    }
}