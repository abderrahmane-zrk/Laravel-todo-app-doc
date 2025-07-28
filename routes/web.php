<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

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

    // ✅ Route تسجيل الخروج (ضروري للهيدر)
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});

require __DIR__.'/auth.php';
