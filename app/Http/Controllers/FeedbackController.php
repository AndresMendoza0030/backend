<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FeedbackController extends Controller
{
    public function submitFeedback(Request $request)
    {
        $username = $request->session()->get('username');
        $feedback = $request->input('feedback');
        $capture = $request->file('capture');

        if ($capture) {
            $filename = $capture->store('captures', 'public');
        } else {
            $filename = null;
        }

        DB::table('feedback')->insert([
            'username' => $username,
            'feedback' => $feedback,
            'capture' => $filename,
        ]);

        return redirect()->route('dashboard.index')->with('message', 'Gracias por tu retroalimentación.');
    }
}
