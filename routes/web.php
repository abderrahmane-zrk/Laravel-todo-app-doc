<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\PermissionManagementController;



// ✅ إعادة توجيه أي زيارة للجذر إلى /tasks
Route::get('/', fn () => redirect('/tasks'));


// ✅ راوتات المشرفين فقط – لوحة التحكم
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
    Route::get('/', fn () => view('admin.dashboard'))->name('dashboard');


    // إدارة المستخدمين
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::get('/users/fetch', [UserManagementController::class, 'fetch'])->name('users.fetch');
    Route::post('/users/delete', [UserManagementController::class, 'delete'])->name('users.delete');

    Route::get('/users/{user}/edit', [UserManagementController::class, 'getUser'])->name('users.get');
    Route::post('/users/{user}/update', [UserManagementController::class, 'update'])->name('users.update');


    Route::get('/permissions-data/{user}', [UserManagementController::class, 'getPermissions'])->name('permissions.data');
    Route::post('/permissions-update/{user}', [UserManagementController::class, 'updatePermissions'])->name('permissions.update');

    Route::get('/permissions', [PermissionManagementController::class, 'index'])->name('permissions');
    Route::get('/permissions-fetch', [PermissionManagementController::class, 'fetch'])->name('permissions.fetch');
    Route::post('/permissions-create', [PermissionManagementController::class, 'create'])->name('permissions.create');


    // ✅ إحضار المستخدمين المرتبطين بصلاحية معينة
    Route::get('/permissions-users/{permission}', [PermissionManagementController::class, 'getUsersWithPermission'])
        ->name('permissions.users');

    // ✅ حذف صلاحيات متعددة
    Route::post('/permissions/delete-multiple', [PermissionManagementController::class, 'deleteMultiplePermissions'])
        ->name('permissions.deleteMultiple');

   

});


// ✅ هذه المجموعة مخصصة للمستخدمين المسجلين فقط
Route::middleware(['auth'])->group(function () {

    // ✅ إعادة توجيه dashboard بناءً على الدور (اختياري إذا عُولج داخل الكنترولر)
    Route::get('/dashboard', fn () => redirect('/tasks'))->name('dashboard');

    // ✅ المهام – عرض، إضافة، تحديث الحالة → للجميع
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/bulk-update', [TaskController::class, 'bulkToggle'])->name('tasks.bulk-update');

    // ✅ حذف المهام – فقط لمن لديه صلاحية delete tasks
    Route::post('/tasks/delete-multiple', [TaskController::class, 'deleteMultiple'])
        ->middleware('permission:delete tasks')
        ->name('tasks.deleteMultiple');

    // ✅ رفع مرفق لمهمة – للجميع
    Route::post('/tasks/{task}/attachments', [TaskController::class, 'uploadAttachment'])->name('tasks.attachments.upload');

    // ✅ حذف مرفق – فقط للمشرف
    Route::delete('/attachments/{id}', [TaskController::class, 'deleteAttachment'])
        ->name('tasks.attachments.delete');

    // ✅ عرض مرفقات مهمة – للجميع
    Route::get('/tasks/{task}/attachments', [TaskController::class, 'attachments'])->name('tasks.attachments');

    // ✅ صفحة الوثائق – للجميع
    Route::get('/attachments', [TaskAttachmentController::class, 'index'])->name('attachments.index');
    Route::get('/attachments/fetch', [TaskAttachmentController::class, 'fetch'])->name('attachments.fetch');
    Route::get('/attachments/download/{id}', [TaskAttachmentController::class, 'download'])->name('attachments.download');

    Route::post('/attachments/delete-multiple', [TaskAttachmentController::class, 'deleteMultiple'])
    ->name('attachments.deleteMultiple');


    // ✅ رفع وثيقة عامة – فقط للمشرف
    Route::post('/attachments/upload-general', [TaskAttachmentController::class, 'uploadGeneral'])
        ->name('attachments.uploadGeneral');

    // ✅ ملف المستخدم الشخصي – للجميع
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // المهام الموجهة (التي استُلمت من مشرف)
    Route::get('/assigned-tasks', [AssignedTaskController::class, 'index'])->name('tasks.assigned');
    Route::get('/assigned-tasks/fetch', [AssignedTaskController::class, 'fetch'])->name('tasks.assigned.fetch');

    // ✅ تسجيل الخروج
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    // ✅ صفحة "توجيه المهام"
    Route::get('/tasks/assign', [TaskController::class, 'assignIndex'])->name('tasks.assign.index');

    // ✅ API: جلب المهام الموجهة
    Route::get('/tasks/assign/fetch', [TaskController::class, 'fetchAssignedTasks'])->name('tasks.assignedbypermitted.fetch');

    // ✅ API: توجيه مهمة موجودة إلى مستخدمين آخرين
    Route::post('/tasks/assign', [TaskController::class, 'assign'])->name('tasks.assign');


    
});

// ✅ تضمين الراوتات الخاصة بالمصادقة (login/register/etc)
require __DIR__.'/auth.php';