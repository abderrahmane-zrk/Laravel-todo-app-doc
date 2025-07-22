<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'reference', 'status', 'started_at', 'completed_at'];

    protected $dates = ['started_at', 'completed_at'];
}
