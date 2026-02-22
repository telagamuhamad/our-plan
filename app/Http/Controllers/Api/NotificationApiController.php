<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationApiController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user->couple_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus terhubung dengan pasangan untuk melihat notifikasi.'
                ], 400);
            }

            $notifications = $user->notifications()
                ->with(['actor', 'post'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->query('per_page', 20));

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'unread_count' => $user->unreadNotificationsCount(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        try {
            $user = Auth::user();
            $count = $user->unreadNotificationsCount();

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($notificationId)
    {
        try {
            $user = Auth::user();
            $notification = $user->notifications()->findOrFail($notificationId);

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sebagai telah dibaca.',
                'data' => $notification
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            $user->unreadNotifications()->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sebagai telah dibaca.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a notification.
     */
    public function destroy($notificationId)
    {
        try {
            $user = Auth::user();
            $notification = $user->notifications()->findOrFail($notificationId);

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notifikasi dihapus.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
