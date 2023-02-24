<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentsLike extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'comment_id'];
}
