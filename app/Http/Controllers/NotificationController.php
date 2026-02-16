<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Exception;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index()
    {
        try {
            $user = Auth::user();

            if (!$user->couple_id) {
                return back()->with('error', 'Anda harus terhubung dengan pasangan untuk melihat notifikasi.');
            }

            $notifications = $user->notifications()
                ->with(['actor', 'post'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('notifications.index', [
                'notifications' => $notifications,
                'unreadCount' => $user->unreadNotificationsCount(),
            ]);
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get unread notifications count (AJAX).
     */
    public function unreadCount()
    {
        try {
            $user = Auth::user();
            $count = $user->unreadNotificationsCount();

            return response()->json([
                'success' => true,
                'count' => $count,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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

            return back()->with('success', 'Semua notifikasi ditandai sebagai telah dibaca.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
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

            return back()->with('success', 'Notifikasi dihapus.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
