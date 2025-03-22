<?php

namespace App\Http\Controllers;
use App\Notifications\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all unread notifications
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $notificationsCount = Auth::user()->unreadNotifications->count();
        $notifications = Auth::user()->unreadNotifications;

        return response()->json([
            'notifications' => $notifications,
            'count' => $notificationsCount
        ]);
    }

    /**
     * Mark Notification as read
     * @param string $id Notification ID
     * @return JsonResponse
     */
    public function markAsRead(string $id): JsonResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return response()->json($notification);
    }

    function test(): JsonResponse
    {
        $user = Auth::user();
        $task = 'This project contains inappropriate content';

        $notification = new Report($task);
        $user->notify($notification);

        return response()->json(['message' => 'Notification Successfully Sent']);
    }
}