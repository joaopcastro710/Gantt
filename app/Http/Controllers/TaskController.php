<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


class TaskController extends Controller 
{
    public function index()
    {
        try {
            $tasks = Task::all();
            return response()->json($tasks, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'deadline' => 'required|date',
        ]);

        $task = new Task();
        $task->title = $request->title;
        $task->start_date = $request->start_date;
        $task->end_date = $request->end_date;
        $task->deadline = $request->deadline;
        $task->save();

        return redirect()->route('tasks.index');
    }

    public function update(Request $request, $id) {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'deadline' => 'required|date',
        ]);

        $task = Task::findOrFail($id);
        $task->title = $request->title;
        $task->start_date = $request->start_date;
        $task->end_date = $request->end_date;
        $task->deadline = $request->deadline;
        $task->save();

        return redirect()->route('tasks.index');
    }

    public function destroy($id) {
        $task = Task::findOrFail($id);
        $task->delete();
    
        return response()->json(['message' => 'Task deleted successfully.']);
    }
    
}