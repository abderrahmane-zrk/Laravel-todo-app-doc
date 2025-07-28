<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $fillable = ['task_id', 'filename', 'original_name', 'mime_type', 'size','user_id'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
