<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Aquí puedes obtener los datos como lo hacías en Flask
        $username = $request->session()->get('username');

        $recentDocuments = get_recent_documents($username);
        $notifications = get_notifications($username);
        $userTasks = get_user_tasks($username);
        $favoriteDocuments = get_favorite_documents($username);
        $sharedDocuments = get_shared_documents($username);
        $userEvents = get_user_events($username);

        return view('dashboard', [
            'recent_documents' => $recentDocuments,
            'notifications' => $notifications,
            'user_tasks' => $userTasks,
            'favorite_documents' => $favoriteDocuments,
            'shared_documents' => $sharedDocuments,
            'user_events' => $userEvents,
        ]);
    }
}
