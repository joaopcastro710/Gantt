<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskActionController;
use App\Http\Controllers\TaskTemplateController;

/*
Route::get('/tasks', [TaskController::class, 'index']);
Route::post('/tasks', [TaskController::class, 'store']);
Route::put('/tasks/{id}', [TaskController::class, 'update']);
Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
*/

Route::apiResource('task-actions', TaskActionController::class);

Route::apiResource('task-templates', TaskTemplateController::class)->only(['index', 'show']);
