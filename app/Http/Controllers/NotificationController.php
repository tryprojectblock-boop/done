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
     * Poll for new notifications (for live toaster).
     */
    public function poll(Request $request): JsonResponse
    {
        $user = $request->user();
        $lastCount = (int) $request->get('last_count', 0);
        $unreadCount = $user->unread_notification_count;

        $response = [
            'unread_count' => $unreadCount,
            'latest_notification' => null,
        ];

        // If there are more notifications than before, get the latest one
        if ($unreadCount > $lastCount) {
            $latest = $user->appNotifications()->whereNull('read_at')->first();
            if ($latest) {
                $response['latest_notification'] = [
                    'id' => $latest->id,
                    'type' => $latest->type,
                    'title' => $latest->title,
                    'message' => $latest->message,
                    'icon' => $latest->icon,
                    'color' => $latest->color,
                    'url' => $this->getNotificationUrl($latest),
                ];
            }
        }

        return response()->json($response);
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
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $count = $request->user()->appNotifications()->whereNull('read_at')->count();

        $request->user()->appNotifications()->whereNull('read_at')->update([
            'read_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', $count > 0
            ? "Marked {$count} notification(s) as read."
            : 'All notifications are already read.');
    }

    /**
     * Delete all notifications.
     */
    public function destroyAll(Request $request): RedirectResponse
    {
        $count = $request->user()->appNotifications()->count();

        $request->user()->appNotifications()->delete();

        return back()->with('success', $count > 0
            ? "Deleted {$count} notification(s)."
            : 'No notifications to delete.');
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
            Notification::TYPE_TASK_CREATED,
            Notification::TYPE_TASK_STATUS => $data['task_url'] ?? null,
            Notification::TYPE_CHANNEL_MEMBER_ADDED => $data['channel_url'] ?? null,
            Notification::TYPE_CHANNEL_REPLY_MENTION => $data['thread_url'] ?? null,
            Notification::TYPE_MILESTONE_ASSIGNED,
            Notification::TYPE_MILESTONE_DUE_SOON,
            Notification::TYPE_MILESTONE_COMPLETED,
            Notification::TYPE_MILESTONE_COMMENT => $data['milestone_url'] ?? null,
            Notification::TYPE_DISCUSSION_COMMENT,
            Notification::TYPE_DISCUSSION_ADDED => $data['discussion_url'] ?? null,
            default => null,
        };
    }
}
