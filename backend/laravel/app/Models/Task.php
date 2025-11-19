<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'status_id',
        'assigned_to_id',
        'created_by_id',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'status_id'      => 'integer',
        'assigned_to_id' => 'integer',
        'created_by_id'  => 'integer',
        'due_date'       => 'date',
        'completed_at'   => 'datetime',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
