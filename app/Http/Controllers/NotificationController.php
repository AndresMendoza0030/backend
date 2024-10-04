<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('notification_id');

        if ($notificationId) {
            DB::table('notifications')
                ->where('id', $notificationId)
                ->update(['is_read' => 1]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
