<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskAttachmentController;


Route::get('/', function () {
    return redirect('/tasks');
});



// ✅ هذه المجموعة مخصصة للمستخدمين المسجلين فقط
Route::middleware(['auth'])->group(function () {

    // ✅ إعادة تعريف dashboard
    Route::get('/dashboard', function () {
        return redirect('/tasks');
    })->name('dashboard');

    // ✅ Routes المهام
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/delete-multiple', [TaskController::class, 'deleteMultiple'])->name('tasks.deleteMultiple');
    Route::post('/tasks/bulk-update', [TaskController::class, 'bulkToggle'])->name('tasks.bulk-update');

    // ✅ Routes المرفقات
    Route::post('/tasks/{task}/attachments', [TaskController::class, 'uploadAttachment'])->name('tasks.attachments.upload');
    Route::delete('/attachments/{id}', [TaskController::class, 'deleteAttachment'])->name('tasks.attachments.delete');
    Route::get('/tasks/{task}/attachments', [TaskController::class, 'attachments'])->name('tasks.attachments');

    
    // ✅ ملف المستخدم الشخصي (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

      // عرض صفحة الوثائق (واجهة Tabulator)
    Route::get('/attachments', [TaskAttachmentController::class, 'index'])->name('attachments.index');

    // جلب المرفقات كـ JSON لـ Tabulator
    Route::get('/attachments/fetch', [TaskAttachmentController::class, 'fetch'])->name('attachments.fetch');

    // تحميل المرفق مباشرة إن أردت استخدامه
    Route::get('/attachments/download/{id}', [TaskAttachmentController::class, 'download'])->name('attachments.download');


    Route::post('/attachments/upload-general', [TaskAttachmentController::class, 'uploadGeneral'])->name('attachments.uploadGeneral');


    // ✅ Route تسجيل الخروج (ضروري للهيدر)
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});

require __DIR__.'/auth.php';