<?php

namespace App\Http\Controllers;

use App\Models\TaskAttachment;
use Illuminate\Http\Request;

class TaskAttachmentController extends Controller
{
    /**
     * عرض صفحة المرفقات (الوثائق).
     */
    public function index()
    {
        return view('attachments.index'); // يفترض وجود blade في resources/views/attachments/index.blade.php
    }

    /**
     * جلب بيانات المرفقات بصيغة JSON لـ Tabulator.
     */
    public function fetch()
    {
        $attachments = TaskAttachment::with(['task:id,title'])
            ->where(function ($query) {
                $query->whereHas('task', function ($q) {
                    $q->where('user_id', auth()->id());
                })->orWhere(function ($q) {
                    $q->whereNull('task_id')
                    ->where('user_id', auth()->id());
                });
            })
            ->select('id', 'task_id', 'user_id', 'filename', 'original_name', 'mime_type', 'size', 'created_at')
            ->latest()
            ->get()
            ->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'task_title' => $attachment->task->title ?? '(غير مرتبطة بمهمة)',
                    'original_name' => $attachment->original_name,
                    'mime_type' => $attachment->mime_type,
                    'size' => round($attachment->size / (1024 * 1024), 2),
                    'created_at' => $attachment->created_at->format('Y-m-d H:i'),
                    'filename' => asset('storage/' . $attachment->filename),
                ];
            });

        return response()->json($attachments);
    }



    /**
     * تحميل المرفق.
     */
    public function download($id)
    {
        $attachment = TaskAttachment::findOrFail($id);
        $filePath = storage_path('app/attachments/' . $attachment->filename);

        if (!file_exists($filePath)) {
            return abort(404, 'الملف غير موجود');
        }

        return response()->download($filePath, $attachment->original_name);
    }

    public function uploadGeneral(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('attachments', 'public');

        TaskAttachment::create([
            'user_id' => auth()->id(),
            'task_id' => null,
            'filename' => $path,
            'original_name' => $uploaded->getClientOriginalName(),
            'mime_type' => $uploaded->getClientMimeType(),
            'size' => $uploaded->getSize(),
        ]);

        return response()->json(['success' => true]);
    }

}
