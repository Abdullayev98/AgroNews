<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Post;

class Comment extends Model
{
    protected $fillable = ['comments','user_id','parent_id','post_id'];
//    static function getlist($id){
//        $comments = Comment::where('post_id','=',$id)->join('users', 'comments.user_id','=','users.id')->get(['comments.*','users.name']);
//        return $comments;
//    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    public function post(){
        return $this->belongsTo(Post::class);
    }
    public function comments(){
        return $this->hasMany(Comment::class,'parent_id');
    }
    public function replies()
    {
        return $this->comments()->with('replies');
    }

    public function likes()
    {
        return $this->hasMany(CommentsLike::class);
    }
    static function addLike($id, $comment_like, $user_id){
        $comment_like->comment_id = $id;
        $comment_like->user_id = $user_id;
        if($comment_like->like != -1){
            $comment_like->like = 1;
        }else{
            $comment_like->like = 0;
        }
        $comment_like->save();

        $comment = Comment::query()->findOrFAil($id);
        $comment->likes +=1;
        $comment->save();
    }
    static function addDisLike($id, $comment_like,$user_id){
        $comment_like->comment_id = $id;
        $comment_like->user_id = $user_id;

        if($comment_like->like != 1){
            $comment_like->like = -1;
        }else{
            $comment_like->like = 0;
        }
        $comment_like->save();

        $comment = Comment::query()->findOrFAil($id);
        $comment->likes -=1;
        $comment->save();
    }

}
