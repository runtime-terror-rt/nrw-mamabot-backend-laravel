<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function notificationLoggedIn(Request $request)
    {
        try {
            $notifications = auth()->user()->notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? null,
                    'type' => $notification->data['type'] ?? null,
                    'message' => $notification->data['message'] ?? null,
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    'created_at_formatted' => $notification->created_at->diffForHumans(),
                    'read_at' => $notification->read_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'fetched Notification Successfully.',
                'data' => $notifications
            ]);
        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Something went wrong, please try again.'
            ]);
        }
    }

    public function globalNotificationList()
    {
        try {
            $notifications = auth()->user()->notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? null,
                    'type' => $notification->data['type'] ?? null,
                    'message' => $notification->data['message'] ?? null,
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    'created_at_formatted' => $notification->created_at->diffForHumans(),
                    'read_at' => $notification->read_at,
                ];
            });

            $announcements = $notifications->filter(function ($item) {
                return $item['type'] === 'announcement';
            })->values();

            return response()->json([
                'success' => true,
                'message' => 'fetched Notification Successfully.',
                'data' => $announcements
            ]);
        } catch (\Exception $e) {

            Log::error($e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Something went wrong, please try again.'
            ]);
        }
    }

    public function deleteGlobalNotification(string $id)
    {
        try {
            $notification = DatabaseNotification::findOrFail($id);
            $notification->delete();
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Delete Notification Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to delete notification',
            ]);
        }
    }

    public function notificationAdmin()
    {
        try {

            if (auth()->user()->hasRole('Admin')) {


                $notifications = DatabaseNotification::all()->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'message' => $notification->data['message'] ?? null,
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                        'created_at_human' => $notification->created_at->diffForHumans(),
                        'read_at' => $notification->read_at ? $notification->read_at->format('Y-m-d H:i:s') : null,
                        'user_id' => $notification->notifiable_id,   // who received it
                        'user_type' => $notification->notifiable_type, // usually App\Models\User
                    ];
                });


                return response()->json([
                    'success' => true,
                    'message' => 'fetched all notification for admin successfully',
                    'data' => $notifications
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Wrong role, only for Admin',
            ]);

        } catch (\Exception $e) {
            Log::error('Notifications:' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Sorry, something went wrong.',
            ]);
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $user = $request->user();

            $user->unreadNotifications->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ]);
        } catch (\Exception $e) {
            Log::error('Notifications mark as read:' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    public function globalNotification(Request $request)
    {
        try {
            $title = $request->input('title');
            $message = $request->input('message');
            $users = User::all(); // or filter by role/team
            foreach ($users as $user) {
                $user->notify(new Announcement($title, $message));
            }

            return response()->json([
                'success' => true,
                'message' => 'Announcement Notification sent',
            ]);
        } catch (\Exception $e) {
            Log::error('Announcement Notifications:' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }
}
