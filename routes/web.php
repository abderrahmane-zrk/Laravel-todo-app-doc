<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

// الصفحة الرئيسية تعيد توجيه المستخدم إلى /tasks
Route::get('/', function () {
    return redirect('/tasks');
});


Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');

Route::post('/tasks/delete-multiple', [TaskController::class, 'deleteMultiple'])->name('tasks.deleteMultiple');
Route::post('/tasks/bulk-toggle', [TaskController::class, 'bulkToggle'])->name('tasks.bulkToggle');
Route::post('/tasks/bulk-update', [TaskController::class, 'bulkToggle'])->name('tasks.bulk-update');

