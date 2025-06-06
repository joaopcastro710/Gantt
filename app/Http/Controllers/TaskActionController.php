<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaskAction;

class TaskActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TaskAction::with('template')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_template_id' => 'required|exists:task_templates,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'deadline' => 'required|date',
            'status' => 'required|string',
        ]);

        $taskAction = TaskAction::create($validated);

        return response()->json($taskAction, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $taskAction = TaskAction::with('template')->findOrFail($id);
        return response()->json($taskAction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $taskAction = TaskAction::findOrFail($id);

        $validated = $request->validate([
            'task_template_id' => 'required|exists:task_templates,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'deadline' => 'required|date',
            'status' => 'required|string',
        ]);

        $taskAction->update($validated);

        return response()->json($taskAction->load('template'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $taskAction = TaskAction::findOrFail($id);
        $taskAction->delete();

        return response()->json(['message' => 'Task action deleted successfully.'], 204);
    }
}
