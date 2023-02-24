<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $notifications = Auth::user()->unreadnotifications;

        if ($notifications)
            return response()->json([
                'message' => 'Data was taken successfully',
                'success' => true,
                'data' => $notifications
            ]);
        return response()->json([
            'message' => 'Something wrong',
            'success' => false,
        ]);
    }
    public function markAsRead($id): \Illuminate\Http\JsonResponse
    {
        if($id){
            auth()->user()->unreadNotifications->where('id',$id)->markAsRead();
        }
        return response()->json([
            'message' => 'a notification is read',
            'success' => true
        ]);
    }
    public function markAsReadAll($id): \Illuminate\Http\JsonResponse

    {
        if($id){
        auth()->user()->unreadNotifications->where('notifiable_id',$id)->markAsRead();
        }
        return response()->json([
            'message' => 'All notification is read',
            'success' => true
        ]);
    }
}
