<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    public $timestamps = false; // only created_at column

    protected $fillable = [
        'task_id',
        'user_id',
        'action_type',
        'field_name',
        'old_value',
        'new_value',
        'created_at',
    ];

    protected $casts = [
        'task_id'    => 'integer',
        'user_id'    => 'integer',
        'created_at' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
