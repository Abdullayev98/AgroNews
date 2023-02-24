<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $locale = app()->getLocale();
        $categories = Category::query()->select('id',
            'title_'.$locale,
            'description_'.$locale,
            DB::raw('CONCAT("'.url('/images').'/", image) as image'))
            ->orderBy('id', 'DESC')
            ->get();
        if ($categories) {
            return response()->json([
                'message' => 'Data was taken successfully',
                'success' => true,
                'data' => $categories

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
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

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
            if($request->hasFile('image')) {
                $file = $request->file('image');
                $imageName = time() . '_' . $file->getClientOriginalName();
                $file->move(\public_path('images/'), $imageName);
                $data['image'] = $imageName;
            }
            Category::query()->create($data);
            return response()->json(['status' => true, 'message' => 'Categoriess data saved successfully!!!']);
        } catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $locale = app()->getLocale();
        $category = Category::query()->select('id','title_'.$locale,'image')->where('id', $id)
            ->with('posts', function ($query) use ($locale) {
                $query->select('category_id','thumbnail','title_'.$locale,'description_'.$locale)->withCount('commentsAll')->get();
        })->get();
        if ($category) {
            return response()->json([
                "message" => "Data are taken succesfully",
                "success" => true,
                "data" => $category
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
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(int $id): \Illuminate\Http\JsonResponse
    {
        $category = Category::query()->find($id);
        if ($category)
            return response()->json([
                'message' => 'Data was taken successfully',
                'success' => true,
                'data' => $category
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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $category = Category::query()->findOrFAil($id);
            $data = $request->all();
            if ($request->hasFile('image')) {
                if (File::exists('images/' . $category->image)) {
                    File::delete('images/' . $category->image);
                }
                $file = $request->file('image');
                $imgName = time() . '_' . $file->getClientOriginalName();
                $file->move(\public_path('images/'), $imgName);
                $data['image'] = $imgName;
            }
            $category->update($data);
            return response()->json(['status' => true, 'message' => 'Categoriess data update successfully!!!']);
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
    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {

        try {
            $category = Category::query()->findOrFAil($id);
            if(File::exists('image/'.$category->image)){
                File::delete('image/'.$category->image);
            };
            $category->delete();
            return response()->json(['status' => true, 'message' => 'Category data delete successfully!!!']);
        }catch (ValidationException $e) {
            return response()->json(array_values($e->errors()));
        }
    }
}
