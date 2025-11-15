<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications
     */
    public function index(Request $request)
    {
        $notifications = $request->user()?->unreadNotifications();

        return NotificationResource::collection($notifications);
    }

    /**
     * Read a notification
     */
    public function read($notificationId)
    {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);

        $notification->markAsRead();

        return redirect($notification->data['link'] ?? '/');
    }
}
