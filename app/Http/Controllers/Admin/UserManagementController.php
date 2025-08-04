<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name');
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $user->assignRole($request->role);

        // ✅ رد خاص بطلبات AJAX
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        // ✅ الرد الافتراضي (عند الإرسال العادي عبر صفحة)
        return redirect()->route('admin.users.index')->with('success', 'تم إنشاء المستخدم وتعيين الدور.');
    }


    public function fetch()
    {
        $users = User::with('roles', 'permissions')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->pluck('name')->implode(', '),
                'permissions' => $user->permissions->pluck('name')->map(fn($p) => "<span class='bg-gray-200 text-xs px-1 py-0.5 rounded mr-1'>$p</span>")->implode(' '),
                'created_at' => $user->created_at->format('Y-m-d H:i'),
            ];
        });

        return response()->json($users);
    }

    public function delete(Request $request)
    {
        $ids = $request->input('ids', []);
        User::whereIn('id', $ids)->delete();

        return response()->json(['success' => true]);
    }


    public function getPermissions(User $user)
    {
        $allPermissions = \Spatie\Permission\Models\Permission::pluck('name');
        $userPermissions = $user->getPermissionNames();

        return response()->json([
            'allPermissions' => $allPermissions,
            'userPermissions' => $userPermissions,
        ]);
    }

    public function updatePermissions(Request $request, User $user)
    {
        $permissions = $request->input('permissions', []);
        $user->syncPermissions($permissions);

        return response()->json(['success' => true]);
    }

    public function getUser(User $user)
    {
        return response()->json($user->only(['id', 'name', 'email']) + [
            'role' => $user->roles->pluck('name')->first()
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $user->syncRoles([$request->role]);

        return response()->json(['success' => true]);
    }
    

}
