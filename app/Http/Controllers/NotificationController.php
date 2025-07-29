<?php

namespace App\Http\Controllers;
use App\Models\ContestApplication;
use App\Notifications\NewContestApplicationNotification;
use App\Notifications\TestNotification;
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

    function test(Request $request): JsonResponse
    {
        $user = Auth::user();

        $type = $request->get('type', 'test');
        $title = $request->get('title', 'Default Title');
        $body = $request->get('body', 'Default body');

        $contestApplication = ContestApplication::first();

        $notification = match ($type) {
            'contest-application' => new NewContestApplicationNotification($contestApplication),
            default => new TestNotification($title, $body),
        };

        $user->notify($notification);

        return response()->json([
            'type' => $type,
            'message' => 'Notification Successfully Sent',
            'notification' => $notification->id,
        ]);
    }
}