<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Mass-assignable attributes
    protected $fillable = ['title', 'description', 'due_date', 'status', 'user_id'];

    // A task belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
