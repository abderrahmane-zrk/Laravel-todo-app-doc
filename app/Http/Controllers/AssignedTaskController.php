<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AssignedTaskController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // ✅ التأكد من وجود صلاحية عرض المهام الموجهة
        //abort_unless(Auth::user()->can('view-assigned-tasks'), 403);

        $users = User::all();
        return view('tasks.assigned', compact('users'));
    }

    public function fetch()
    {
        $tasks = Auth::user()->assignedTasks()->withCount('attachments')->get();

        return response()->json($tasks);
    }
}
