<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::latest('id')->get();
        if($categories->count()) {
            return response()->json([
                'status' => 200,
                'categories' => CategoryResource::collection($categories)
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => "There's no data"
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:2|max:12|unique:categories,title'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                'old' => $request->all()
            ]);
        }
        $unique_id = uniqid() . "_category_" . uniqid();
        $category = Category::create([
            'title' => ucfirst($request->title),
            'unique_id' => $unique_id,
            'user_id' => Auth::id()
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'A category is created',
            'category' => $category
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        if(!is_null($category)) {
            return response()->json([
                'status' => 200,
                'category' => $category
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => "There's no data"
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCategoryRequest  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'title' => "required|min:2|max:20|unique:categories,title,". $category->id
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => "validation error",
                'errors' => $validator->errors(),
                'old' => $request->all()
            ]);
        }

        $category->title = $request->title;
        $category->update();

        return response()->json([
            'status' => 200,
            'message' => 'Updated Successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $categoryName = $category->title;
        $category->delete();
        return response()->json([
            'status' => 200,
            'message' => "Category -" . $categoryName . "- is removed",

        ]);
    }
}
