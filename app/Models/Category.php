<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class Category extends Model
{
    protected $guarded= ['id'];

    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Post::class);
    }
}
