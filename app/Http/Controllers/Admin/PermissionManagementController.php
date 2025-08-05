<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionManagementController extends Controller
{
    // ✅ عرض صفحة إدارة الصلاحيات
    public function index()
    {
        return view('admin.permissions');
    }

    // ✅ جلب الصلاحيات لـ Tabulator
    public function fetch()
    {
        $permissions = Permission::with('roles')->get()->map(function ($perm) {
            return [
                'id' => $perm->id,
                'name' => $perm->name,
                'guard_name' => $perm->guard_name,
                'created_at' => $perm->created_at->format('Y-m-d'),
                'roles' => $perm->roles->pluck('name')->map(fn($name) => "<span class='bg-gray-200 px-2 py-1 rounded text-sm'>$name</span>")->implode(' ')
            ];
        });

        return response()->json($permissions);
    }

    // ✅ إنشاء صلاحية جديدة + (اختياري) ربطها برول
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'role' => 'nullable|string|exists:roles,name',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        // ✅ ربطها بدور إذا تم تحديده
        if ($request->filled('role')) {
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $role->givePermissionTo($permission);
            }
        }

        return response()->json(['success' => true]);
    }

    public function getUsersWithPermission($permissionName)
    {
        $permission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();

        if (! $permission) {
            return response()->json(['error' => 'الصلاحية غير موجودة'], 404);
        }

        $users = \App\Models\User::permission($permissionName)->select('id', 'name', 'email', 'created_at')->get();

        return response()->json($users);
    }


    public function deleteMultiplePermissions(Request $request)
    {
        $request->validate([
            'names' => 'required|array|min:1',
            'names.*' => 'string|exists:permissions,name',
        ]);

        foreach ($request->names as $name) {
            $permission = \Spatie\Permission\Models\Permission::where('name', $name)->first();
            if ($permission) {
                $permission->delete();
            }
        }

        return response()->json(['success' => true]);
    }


    
}
