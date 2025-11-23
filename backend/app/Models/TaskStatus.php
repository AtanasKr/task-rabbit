<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStatus extends Model
{
    use HasFactory;

    protected $table = 'task_statuses';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    public function tasks() {
        return $this->hasMany(Task::class, 'status_id');
    }
}
