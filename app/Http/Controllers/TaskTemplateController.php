<?php

namespace App\Http\Controllers;

use App\Models\TaskTemplate;

class TaskTemplateController extends Controller
{
    public function index()
    {
        return TaskTemplate::all();
    }

    public function show($id)
    {
        return TaskTemplate::findOrFail($id);
    }
}