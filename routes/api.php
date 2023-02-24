<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use \App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::get("/weather", [PostController::class, 'weather']);
Route::get("/exchanges", [PostController::class, 'exchanges']);

Route::middleware(['auth:api', 'api.localization'])->group(function () {
    Route::post("categories/{id}", [CategoryController::class, 'update']);
    Route::resource('categories', CategoryController::class);
    Route::post("posts/{id}", [PostController::class, 'update']);
    Route::resource('posts', PostController::class);
    Route::post("comments/{id}", [CommentController::class, 'update']);
    Route::resource('comments', CommentController::class);

    Route::post("post/like/{id}", [PostController::class, 'like']);
    Route::post("post/dislike/{id}", [PostController::class, 'dislike']);

    Route::get("/post/search", [PostController::class, 'search']);

    Route::post("comment/like/{id}", [CommentController::class, 'like']);
    Route::post("comment/dislike/{id}", [CommentController::class, 'dislike']);

    Route::post("favorite", [PostController::class, 'saved']);
    Route::get("favorite/list", [PostController::class, 'getSaved']);

    Route::get("notification", [NotificationController::class, 'index']);

    Route::post("notification/read/{id}", [NotificationController::class, 'markAsRead']);
    Route::post("notification/read_all/{notifiable_id}", [NotificationController::class, 'markAsReadAll']);
});
