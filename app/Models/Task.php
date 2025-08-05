<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;


    protected $fillable = ['title', 'reference', 'status', 'started_at', 'completed_at', 'comment', 'user_id'];



    protected $dates = ['started_at', 'completed_at'];

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'assigned_task_user')->withTimestamps();
    }


}
