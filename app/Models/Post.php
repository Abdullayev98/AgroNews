<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class Post extends Model
{
    protected $guarded = ['id'];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
    return $this->hasMany(Comment::class)->where('parent_id','=',0);
}
    public function commentsAll(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }
    public function likes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PostLike::class);

    }
    public function savedPost(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PostSaved::class);

    }

    public static function addLike($id, $post_like,$user_id){

        $post_like->post_id = $id;
        $post_like->user_id = $user_id;

        if($post_like->like != -1){
            $post_like->like = 1;
        }else{
            $post_like->like = 0;
        }
        $post_like->save();

        $post = Post::query()->findOrFAil($id);
        $post->likes +=1;
        $post->save();
    }

    public static function addDisLike($id, $post_like,$user_id){

        $post_like->post_id = $id;
        $post_like->user_id = $user_id;

        if($post_like->like != 1){
            $post_like->like = -1;
        }else{
            $post_like->like = 0;
        }
        $post_like->save();

        $post = Post::query()->findOrFAil($id);
        $post->likes -=1;
        $post->save();
    }
}
