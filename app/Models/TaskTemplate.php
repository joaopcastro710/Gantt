<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTemplate extends Model{
    use HasFactory;

    protected $fillable = [
        'title',
        'description'
    ];

    public function actions (){
        return $this->hasMany(TaskAction::class);
    }
}