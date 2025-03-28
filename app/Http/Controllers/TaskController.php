<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // Retorna todas as tarefas
    public function index()
    {
        return response()->json(Task::all());
    }

    // Cria uma nova tarefa
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'deadline' => 'required|date',
        ]);

        $task = Task::create($validated);

        return response()->json($task, 201);
    }

    // Atualiza uma tarefa
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'deadline' => 'required|date',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    // Apaga uma tarefa
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Tarefa eliminada com sucesso.']);
    }
}
