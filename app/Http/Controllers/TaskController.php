<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Support\Facades\Storage;
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

    public function attachments(Task $task)
    {
        $attachments = $task->attachments->map(function ($file) {
            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'mime_type' => $file->mime_type,
                'size' => $file->size,
                'url' => Storage::url($file->filename),
                'created_at' => $file->created_at->toDateTimeString(),
            ];
        });

        return response()->json($attachments);
    }

    public function uploadAttachment(Request $request, Task $task)
    {
        $request->validate([
            'attachment' => 'required|file|max:20000', // 5MB كحد أقصى
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');

            $attachment = $task->attachments()->create([
                'filename' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);

            return response()->json([
                'success' => true,
                'attachment' => [
                    'id' => $attachment->id,
                    'original_name' => $attachment->original_name,
                    'mime_type' => $attachment->mime_type,
                    'size' => $attachment->size,
                    'url' => Storage::url($attachment->filename),
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'لم يتم رفع أي ملف'], 400);
    }

    public function deleteAttachment($id)
    {
        $attachment = TaskAttachment::findOrFail($id);

        // حذف من التخزين
        if (Storage::disk('public')->exists($attachment->filename)) {
            Storage::disk('public')->delete($attachment->filename);
        }

        $attachment->delete();

        return response()->json(['success' => true]);
    }
}
