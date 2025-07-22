<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'reference' => 'nullable|string|max:100'
        ]);

        $task = Task::create([
            'title' => $request->title,
            'reference' => $request->reference,
        ]);

        return response()->json(['success' => true, 'task' => $task]);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $tasks = Task::orderByDesc('id')->get();
            return response()->json(['success' => true, 'tasks' => $tasks]);
        }

        $tasks = Task::orderByDesc('id')->get();
        return view('tasks.index', compact('tasks'));
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
        $status = $request->input('status', 'pending');

        $data = [
            'status' => $status,
            'started_at' => $status === 'in_progress' ? now() : null,
            'completed_at' => $status === 'done' ? now() : null,
        ];

        Task::whereIn('id', $ids)->update($data);

        return response()->json(['success' => true]);
    }



}
