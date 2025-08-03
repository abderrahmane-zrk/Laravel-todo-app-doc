<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskAttachmentController;
use App\Http\Controllers\ProfileController;

Route::get('/', fn () => redirect('/tasks'));

// ✅ هذه المجموعة مخصصة للمستخدمين المسجلين فقط
Route::middleware(['auth'])->group(function () {

    // ✅ إعادة توجيه dashboard
    Route::get('/dashboard', fn () => redirect('/tasks'))->name('dashboard');

    // ✅ المهام – عرض، إضافة، تحديث الحالة → للجميع
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/bulk-update', [TaskController::class, 'bulkToggle'])->name('tasks.bulk-update');

    // ✅ حذف المهام – فقط للمشرف
        Route::post('/tasks/delete-multiple', [TaskController::class, 'deleteMultiple'])
        ->middleware(['auth', 'role:admin'])
        ->name('tasks.deleteMultiple');


    // ✅ رفع مرفق لمهمة – للجميع
    Route::post('/tasks/{task}/attachments', [TaskController::class, 'uploadAttachment'])->name('tasks.attachments.upload');

    // ✅ حذف مرفق – فقط للمشرف
    Route::delete('/attachments/{id}', [TaskController::class, 'deleteAttachment'])
        ->middleware('role:admin')
        ->name('tasks.attachments.delete');

    // ✅ عرض مرفقات مهمة – للجميع
    Route::get('/tasks/{task}/attachments', [TaskController::class, 'attachments'])->name('tasks.attachments');

    // ✅ صفحة الوثائق – للجميع
    Route::get('/attachments', [TaskAttachmentController::class, 'index'])->name('attachments.index');
    Route::get('/attachments/fetch', [TaskAttachmentController::class, 'fetch'])->name('attachments.fetch');
    Route::get('/attachments/download/{id}', [TaskAttachmentController::class, 'download'])->name('attachments.download');

    // ✅ رفع وثيقة عامة – فقط للمشرف

    
    Route::post('/attachments/upload-general', [TaskAttachmentController::class, 'uploadGeneral'])
    ->middleware(['auth', 'role:admin'])
    ->name('attachments.uploadGeneral');


    // ✅ ملف المستخدم الشخصي – للجميع
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ تسجيل الخروج
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});

require __DIR__.'/auth.php';
