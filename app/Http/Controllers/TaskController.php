<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;


class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = Task::orderBy('id', 'desc')->get();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'tasks' => $tasks
            ]);
        }

        return view('tasks.index', compact('tasks'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'completed' => false,
        ]);

        return response()->json([
            'success' => true,
            'task' => $task
        ]);
    }

    public function deleteMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        Task::whereIn('id', $request->ids)->delete();

        return response()->json(['success' => true]);
    }

    public function bulkToggle(Request $request)
    {
        $ids = $request->input('ids', []);
        $completed = $request->input('completed', false);

        Task::whereIn('id', $ids)->update(['completed' => $completed]);

        return response()->json(['success' => true]);
    }

}
