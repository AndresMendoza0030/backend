<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function addTask(Request $request)
    {
        $username = $request->session()->get('username');
        $taskDescription = $request->input('task_description');
        $taskDueDate = $request->input('task_due_date');

        DB::table('tasks')->insert([
            'username' => $username,
            'description' => $taskDescription,
            'due_date' => $taskDueDate,
        ]);

        return redirect()->route('dashboard.index');
    }
}
