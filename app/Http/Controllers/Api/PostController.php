<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostStoreRequest;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostSaved;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
   public function dateBetween(): \Illuminate\Database\Eloquent\Builder
   {
       $date = request('date');
       return match ($date) {
           // today's data
           "day" => Post::query()->whereDate('created_at', Carbon::today()),
           // Data from 1 week ago to today
           "week" => Post::query()->whereBetween('created_at', [Carbon::now()->subWeek()->format("Y-m-d"), Carbon::now()]),
           // Data from 1 month ago to today
           "month" => Post::query()->whereBetween('created_at', [Carbon::now()->subMonth()->format("Y-m-d"), Carbon::now()]),
           // Data from 1 year ago to today
           "year" => Post::query()->whereBetween('created_at', [Carbon::now()->subYear()->format("Y-m-d"), Carbon::now()]),

//            "month" => Post::query()->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y')),    //this month's data  only December
//            "year" => Post::query()->whereYear('created_at', date("Y")),                                          //this year's data  only 2022
           default => Post::query(),
       };
   }
    public function indexQuery(): array|\Illuminate\Database\Eloquent\Collection
    {
        $locale = app()->getLocale();
        $data = $this->dateBetween();
        return $data->select('id', 'title_'.$locale, 'description_'.$locale, 'content_'.$locale,'likes', 'category_id',
            DB::raw('CONCAT("' . url('/postImages') . '/", thumbnail) as thumbnail'), 'created_at')
            ->with(['category'=>function ($query) use ($locale){
                    $query->select('id','title_'.$locale,DB::raw('CONCAT("' . url('/images') . '/", image) as image'));
                },'savedPost'=>function ($query){
                    $query->select('post_id','status');
                }]
            )
            ->withCount('commentsAll as comments_count')
            ->orderBy('id','Desc')
            ->get();
    }
   public function index(): \Illuminate\Http\JsonResponse
    {
        $posts = $this->indexQuery();
        if ($posts) {
            return response()->json([
                "message" => "Data are taken successfully",
                "success" => true,
                "data" => $posts
            ]);
        }
        return response()->json([
            'message' => 'Something wrong',
            'success' => false,
        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
//    public function create()
//    {
//
//    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PostStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->all();
            if ($request->hasFile('thumbnail')) {
                $file = $request->file('thumbnail');
                $image_name = time() . '_' . $file->getClientOriginalName();
                $file->move(\public_path('postImages/'), $image_name);
                $data['thumbnail'] = $image_name;
            }
            Post::query()->create($data);

            return response()->json(['status' => true, 'message' => 'Posts data saved successfully!!!']);
        } catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $locale = app()->getLocale();
        $post = Post::query()->select(
            'id', 'title_'.$locale, 'description_'.$locale, 'content_'.$locale,
            'likes', 'category_id', DB::raw('CONCAT("' . url('/postImages') . '/", thumbnail) as thumbnail'))
            ->where('id', $id)
            ->with(
                ['category' => function ($query) use ($locale){
                    $query->select('id','title_'.$locale,DB::raw('CONCAT("' . url('/images') . '/", image) as image'));
            },'comments.user:id,name',
            'comments.replies.user:id,name']

            )->withCount('commentsAll as comments_count')->get();
        if ($post) {
            return response()->json([
                "message" => "Data are taken successfully",
                "success" => true,
                "data" => $post
            ]);
        }

        return response()->json([
                'message' => 'Something wrong',
                'success' => false,
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(int $id): \Illuminate\Http\JsonResponse
    {
        $locale = app()->getLocale();
        $post = Post::query()->select(
            'id', 'title_'.$locale, 'description_'.$locale, 'content_'.$locale,
            'likes', 'category_id', DB::raw('CONCAT("' . url('/postImages') . '/", thumbnail) as thumbnail'))
            ->find($id);
        if ($post)
            return response()->json([
                'message' => 'Data was taken successfully',
                'success' => true,
                'data' => $post
            ]);
        return response()->json([
            'message' => 'Something wrong',
            'success' => false,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PostStoreRequest $request,int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $post = Post::query()->findOrFAil($id);
            $data = $request->all();
            if ($request->hasFile('thumbnail')) {
                if (File::exists('postImages/' . $post->thumbnail)) {
                    File::delete('postImages/' . $post->thumbnail);
                }
                $file = $request->file('thumbnail');
                $imgName = time() . '_' . $file->getClientOriginalName();
                $file->move(\public_path('postImages/'), $imgName);
                $data['thumbnail'] = $imgName;
            }

            $post->update($data);
            return response()->json(['status' => true, 'message' => 'Post data update successfully!!!']);
        } catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $post = Post::query()->findOrFAil($id);
            if (File::exists('postImages/' . $post->thumbnail)) {
                File::delete('postImages/' . $post->thumbnail);
            };
            $post->delete();
            return response()->json(['status' => true, 'message' => 'Post data delete successfully!!!']);
        } catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }

    public function like(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $user_id = Auth::id();
            $post_like = PostLike::query()->where([
                ['post_id', $id],
                ['user_id', $user_id]
            ])->first();

            if (empty($post_like)) {
                $post_like = new PostLike();
            }
            Post::addlike($id, $post_like, $user_id);
            return response()->json(['status' => true, 'message' => 'You like this message']);

        } catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }

    public function dislike(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $user_id = Auth::id();
            $post_like = PostLike::where([
                ['post_id', $id],
                ['user_id', $user_id]
            ])->first();

            if (empty($post_like)) {
                $post_like = new PostLike();
            }
            Post::addDislike($id, $post_like, $user_id);
            return response()->json(['status' => true, 'message' => 'You dislike this message']);

        } catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $locale = app()->getLocale();
        $query = $request->get('query');
        $filterResult = Post::query()->select(
        'id', 'title_'.$locale, 'description_'.$locale, 'content_'.$locale,
        'likes', 'category_id', DB::raw('CONCAT("' . url('/postImages') . '/", thumbnail) as thumbnail'))
            ->where('description_'.$locale, 'LIKE', '%'.$query. '%')->get();
        return response()->json($filterResult);
    }
    public function weather(): array
    {
        $lat = 41.31119;
        $lon = 69.27970;
        $appid = "f8f9fc8ab06d8a81390b1a9dba408304";
        $type = "metric";
        $url = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&appid={$appid}&units={$type}";
        $res = Http::get($url)->object();
           foreach ($res->list as $item) {
               $arr[] = [
                   'temp' => $item->main->temp,
                   'icon' => $item->weather[0]->icon,
                   'date' => $item->dt_txt,
               ];
           }
       return $arr;
    }
    public function exchanges():array
    {
        $url = "https://cbu.uz/uz/arkhiv-kursov-valyut/json/";
        $res = Http::get($url)->object();
        $objects = (object) $res;
        foreach ($objects as $item) if ($item->Ccy=="USD" || $item->Ccy=="EUR" || $item->Ccy=="RUB"){
            $arr[] = [
                'id' => $item->id,
                'Ccy' => $item->Ccy,
                'Rate' => $item->Rate,
                'Diff' => $item->Diff
            ];
        }
        return $arr;
    }

    public function saved(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user_id = Auth::id();
            $data = PostSaved::query()->where([
                ['post_id', $request->post_id],
                ['user_id', $user_id]
            ])->first();
            if(empty($data)){
                DB::table('post_saved')->insert([
                    'post_id'=>$request->post_id,
                    'user_id'=>$user_id,
                    'status'=> 1
                ]);
                return response()->json(['status' => true, 'message' => 'You have saved this post to saved list']);
            }elseif($data->status == 0){
                $data->status = 1;
            }else{
                $data->status = 0;
            }
            $data->save();
            return response()->json(['status' => true, 'message' => 'Your data are saved successfully']);

        } catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }

    public function getSaved(): \Illuminate\Http\JsonResponse
    {
        $locale = app()->getLocale();
        $posts = Post::query()->select('posts.id', 'posts.title_'.$locale, 'posts.description_'.$locale, 'posts.content_'.$locale,'posts.likes', 'posts.category_id',
            DB::raw('CONCAT("' . url('/postImages') . '/", posts.thumbnail) as thumbnail'))
            ->join('post_saved as ps','posts.id','=','ps.post_id')
            ->where([
                ['ps.status', 1],
                ['ps.user_id', Auth::id()]
            ])
            ->withCount('commentsAll as comments_count')
            ->get();

        if ($posts) {
            return response()->json([
                "message" => "Saved list are taken successfully",
                "success" => true,
                "data" => $posts
            ]);
        }

        return response()->json([
            'message' => 'Something wrong',
            'success' => false,
        ]);
    }
}
