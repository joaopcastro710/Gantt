<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_template_id',
        'start_date',
        'end_date',
        'deadline',
        'status',
    ];
    public function template(){
        return $this->belongsTo(TaskTemplate::class, 'task_template_id');
    }
}