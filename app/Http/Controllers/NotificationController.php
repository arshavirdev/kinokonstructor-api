<?php

namespace App\Http\Controllers;
use App\Notifications\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Request;

class NotificationController extends Controller
{
    /**
     * Get all unread notifications
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $authUser = auth()->user();

        $notificationsCount = $authUser->unreadNotifications->count();
        $notifications = $authUser->unreadNotifications;
        $readNotifications = $authUser->readNotifications;

        return response()->json([
            'unread_notifications' => $notifications,
            'read_notifications' => $readNotifications,
            'count' => $notificationsCount
        ]);
    }

    /**
     * Mark Notification as read
     * @param string $id Notification ID
     * @return JsonResponse
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $authUser = auth()->user();
        $ids = $request->get('ids', []);

        if (empty($ids) && $authUser->unreadNotifications->isNotEmpty()) {
            $authUser->unreadNotifications->markAsRead();
            return response()->json([]);
        }

        $authUser->unreadNotifications->whereIn('id', $ids)->markAsRead();
        return response()->json([]);
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