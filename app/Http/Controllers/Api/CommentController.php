<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Http\Controllers\Controller;
use App\Models\CommentsLike;
use App\Models\User;
use App\Notifications\ReplyCommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use JetBrains\PhpStorm\NoReturn;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $comments = Comment::all();
        if ($comments)
            return response()->json([
                'message' => 'Data was taken successfully',
                'success' => true,
                'data' => $comments

            ]);
        return response()->json([
            'message' => 'Something wrong',
            'success' => false,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
//    public function create()
//    {
//        //
//    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {

        try{
            $data = $request->all();
            $data['user_id'] = Auth::id();

            if($request->parent_id)
            {
                $data['parent_id'] = $request->parent_id;
                $user_id = Comment::query()->select('user_id')->where('id',$request->parent_id)->first();
                $userAccept = User::query()->where('id',$user_id->user_id)->first();
                Notification::send($userAccept, new ReplyCommentNotification($data));
            }
            Comment::query()->create($data);

            return response()->json(['status' => true, 'message' => 'Comment data saved successfully!!!']);
        } catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//    public function show($id)
//    {
//
//    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $comment = Comment::query()->find($id);
        if ($comment)
            return response()->json([
                'message' => 'Data was taken successfully',
                'success' => true,
                'data' => $comment
            ]);
        return response()->json([
            'message' => 'Something wrong',
            'success' => false,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $comment = Comment::query()->findOrFAil($id);

            $comment->update([
                'comments' => $request->comments,
                'user_id' => Auth::id(),
                'parent_id' => $request->parent_id,
                'post_id' => $request->post_id
            ]);
            return response()->json(['status' => true, 'message' => 'Comment data update successfully!!!']);
        }catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $comment = Comment::query()->findOrFAil($id);
            $comment->delete();
            return response()->json(['status' => true, 'message' => 'Comment data delete successfully!!!']);
        }catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }
    public function like($id)
    {
        try {
            $user_id = Auth::id();
            $comment_like = CommentsLike::query()->where([
                ['comment_id', $id],
                ['user_id', $user_id]
            ])->first();

            if (empty($comment_like)) {
                $comment_like = new CommentsLike();
            }
            Comment::addlike($id, $comment_like, $user_id);
            return response()->json(['status' => true, 'message' => 'You like this message']);

        } catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }

    public function dislike($id){
            try {
                $user_id = Auth::id();
                $comment_like = CommentsLike::query()->where([
                    ['comment_id', $id],
                    ['user_id', $user_id]
                ])->first();

                if (empty($comment_like)) {
                    $comment_like = new CommentsLike();
                }
                Comment::addDislike($id, $comment_like, $user_id);
                return response()->json(['status' => true, 'message' => 'You dislike this message']);

            } catch (ValidationException $e) {
                return response()->json(array_values($e->errors()));
            }
    }
}
