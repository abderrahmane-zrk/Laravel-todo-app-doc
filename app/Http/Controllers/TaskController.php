<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class TaskController extends Controller
{
    use AuthorizesRequests;
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'reference' => $validated['reference'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'status' => 'pending',
            'user_id' => Auth::id(),
            'started_at' => now(),
        ]);

        // إسناد المستخدمين إن وُجدوا
        if (isset($validated['assigned_users'])) {
            $task->assignedUsers()->sync($validated['assigned_users']);
        }

        return response()->json(['success' => true, 'task' => $task]);
    }


    public function index(Request $request)
    {
        $tasks = Task::withCount('attachments')
            ->where('user_id', auth()->id()) // ✅ فقط مهام المستخدم الحالي
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'tasks' => $tasks]);
        }

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
                'user_id' => auth()->id(), // ✅ ربط المرفق بالمستخدم
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



    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,done',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $task->update([
            'title' => $validated['title'],
            'reference' => $validated['reference'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'status' => $validated['status'] ?? $task->status,
        ]);

        // تحديث المستخدمين الموجهة لهم المهمة
        if (isset($validated['assigned_users'])) {
            $task->assignedUsers()->sync($validated['assigned_users']);
        }

        return response()->json(['message' => 'تم تحديث المهمة بنجاح']);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'assigned_users' => 'required|array',
            'assigned_users.*' => 'exists:users,id',
        ]);

        $task = Task::findOrFail($request->task_id);
        $task->assignedUsers()->syncWithoutDetaching($request->assigned_users); // توجيه دون حذف السابق

        return response()->json(['message' => 'تم توجيه المهمة بنجاح']);
    }
    

    public function fetchAssignedTasks()
    {

        // في حالة المشرف، نريد جلب جميع المهام (ليس فقط الموجهة إليه)
        
            $tasks = Task::withCount('attachments')->get();
        

        return response()->json($tasks);
    }

    public function assignIndex()
    {
        $users = User::all();
        return view('tasks.assign_task_to', compact('users'));
    }


    
}
