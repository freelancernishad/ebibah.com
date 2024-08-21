<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Create a new notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $notification = Notification::create([
            'user_id' => $request->user_id,
            'type' => $request->type,
            'message' => $request->message,
            'read' => false,
        ]);

        return response()->json(['message' => 'Notification created successfully', 'notification' => $notification], 201);
    }

    /**
     * Get all notifications for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll()
    {
        $user = Auth::guard('api')->user();
        $notifications = Notification::where('user_id', $user->id)->get();

        return response()->json(['notifications' => $notifications], 200);
    }

    /**
     * Get a specific notification by ID.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        return response()->json(['notification' => $notification], 200);
    }

    /**
     * Mark a notification as read.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update(['read' => true]);

        return response()->json(['message' => 'Notification marked as read'], 200);
    }

    /**
     * Delete a notification.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted successfully'], 200);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        $user = Auth::guard('api')->user();
        Notification::where('user_id', $user->id)->update(['read' => true]);

        return response()->json(['message' => 'All notifications marked as read'], 200);
    }
}
