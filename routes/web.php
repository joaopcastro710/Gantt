<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');    // index, '->name('tasks.index')' might not be correct, CHECK
Route::post('/tasks', [TaskController::class, 'create'])->name('tasks.create'); // create
Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update'); // update
Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy'); // destroy