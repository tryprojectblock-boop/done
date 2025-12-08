<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display all notifications.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $notifications = $user->appNotifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get notifications for dropdown (AJAX).
     */
    public function dropdown(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->appNotifications()->take(10)->get();
        $unreadCount = $user->unread_notification_count;

        $notificationData = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'icon' => $notification->icon,
                'color' => $notification->color,
                'is_read' => $notification->isRead(),
                'time' => $notification->created_at->diffForHumans(),
                'url' => $this->getNotificationUrl($notification),
            ];
        })->values()->toArray();

        return response()->json([
            'notifications' => $notificationData,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get unread count (AJAX).
     */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unread_notification_count,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->appNotifications()->whereNull('read_at')->update([
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, Notification $notification): RedirectResponse|JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $notification->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification deleted.');
    }

    /**
     * Get the URL to redirect to for a notification.
     */
    protected function getNotificationUrl(Notification $notification): ?string
    {
        $data = $notification->data ?? [];

        return match ($notification->type) {
            Notification::TYPE_MENTION,
            Notification::TYPE_TASK_COMMENT,
            Notification::TYPE_TASK_ASSIGNED,
            Notification::TYPE_TASK_STATUS => $data['task_url'] ?? null,
            Notification::TYPE_CHANNEL_MEMBER_ADDED => $data['channel_url'] ?? null,
            Notification::TYPE_CHANNEL_REPLY_MENTION => $data['thread_url'] ?? null,
            Notification::TYPE_CHANNEL_JOIN_REQUEST, 'channel_join_request' => $data['channel_url'] ?? null,
            Notification::TYPE_CHANNEL_JOIN_REJECTED, 'channel_join_rejected' => null,
            default => null,
        };
    }
}
