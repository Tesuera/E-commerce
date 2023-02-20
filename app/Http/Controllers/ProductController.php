<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\ProductPhoto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\User;
use App\Models\Category;
use PDOException;
use Carbon\Carbon;
use App\Http\Resources\OrderResource;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = ProductResource::collection(Product::when(request('keyword'), function ($query) {
            $keyword = request('keyword');
            return $query->where('name','like', '%' . $keyword . '%');
        })->latest('id')->paginate(12));
        if($products->count()) {
            return $products;
        } else {
            return [];
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
     * @param  \App\Http\Requests\StoreProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:50|unique:products,name',
            'price' => 'required|integer|min:1',
            'stock' => 'required|integer|min:1',
            'category' => 'required|exists:categories,id',
            'description' => 'required|min:10|max:500',
            'photos' => 'required',
            'photos.*' => 'file|mimes:png,jpg|max:512'
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }
        $unique_id = uniqid() . "_product_" . uniqid();
        $product = Product::create([
            'name' => $request->name,
            'unique_id' => $unique_id,
            'slug' => Str::slug($request->name),
            'price' => (int) $request->price,
            'stock' => (int) $request->stock,
            'category_id' => (int) $request->category,
            'user_id' => Auth::id(),
            'description' => $request->description,
        ]);

        $photoArr = [];
        if($request->photos) {
            foreach($request->photos as $key=>$photo) {
                $newname = uniqid() . "__product__." . $photo->extension();
                $photo_unique = uniqid() . "_product_photo_" . uniqid();
                $photo->storeAs('public/products', $newname);
                $photoArr[] = new ProductPhoto(["photo" => $newname, "unique_id" => $photo_unique]);
            }
            $product->photos()->saveMany($photoArr);
        }
        return response()->json([
            'status' => 200,
            'message' => "A product created successfully",
            'product' => $product
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::where('slug', $id)->first();

        if(is_null($product)) {
            return response()->json([
                'status' => 204,
                'message' => "No such content is found"
            ]);
        }
        return response()->json([
            'status' => 200,
            'product' => new ProductResource($product)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if(is_null($product)) {
            return response()->json([
                'status' => 204,
                'message' => "No such product is found"
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2|max:50|unique:products,name,' . $id,
            'price' => 'required|integer|min:1',
            'stock' => 'required|integer|min:1',
            'category' => 'required|exists:categories,id',
            'description' => 'required|min:10|max:500',
            'photos' => 'nullable',
            'photos.*' => 'file|mimes:png,jpg|max:512'
        ]);
        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }

        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->category_id = $request->category;
        $product->description = $request->description;

        if($request->photos) {
            // remove from storage and database
            $curPhotoArr = [];
            foreach($product->photos as $photo) {
                Storage::delete('public/products/'. $photo->photo);
                $curPhotoArr[] = $photo->id;
            }
            ProductPhoto::destroy($curPhotoArr);

            // add new photos to storage and db
            $newPhotoArr = [];
            foreach($request->photos as $photo) {
                $newname = uniqid() . "__product__." . $photo->extension();
                $photo_unique = uniqid() . "_product_photo_" . uniqid();
                $photo->storeAs('public/products', $newname );
                $newPhotoArr[] = new ProductPhoto([
                    'photo' => $newname,
                    'unique_id' => $photo_unique
                ]);
            }
            $product->photos()->saveMany($newPhotoArr);
        }
        $product->update();
        return response()->json([
            'status' => 200,
            'message' => "Updated successfully",
            'product' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        try {
            if(!is_null($product)) {
                $name = $product->name;
                if($product->photos->count()) {
                    $photoArr = [];
                    foreach($product->photos as $photo) {
                        // remove from storage
                        Storage::delete('public/products/'. $photo);
                        // push to photo array
                        $photoArr[] = $photo->id;
                    }
                    ProductPhoto::destroy($photoArr);
                }
                $product->delete();
                return response()->json([
                    'status' => 200,
                    'message' => $name . "is deleted successfully"
                ]);
            } else {
                return response()->json([
                    'status' => 204,
                    'message' => 'no such product is found'
                ]);
            }
        } catch (PDOException $err) {
            return response()->json([
                'status' => 500,
                'message' => "Something went wrong. Try again.",
                'errors' => $err->getMessage()
            ]);
        }
    }

    public function chart() {
        $orders = Order::all()->count();
        $users = User::all()->count();
        $categories = Category::all()->count();
        $products = Product::all()->count();

        $latestOrders = OrderResource::collection(Order::latest()->limit(5)->get());

        $productMonths = array();
        $productCounts = array();

        $orderMonths = array();
        $orderCounts = array();

        for ($i = 11; $i >= 0; $i--) {
            $productMonths[] = Carbon::today()->startOfMonth()->subMonth($i)->format('M');
            $productCounts[] = Product::whereMonth('created_at', Carbon::today()->startOfMonth()->subMonth($i)->format('m'))->count();

            $orderMonths[] =  Carbon::today()->startOfMonth()->subMonth($i)->format('M');
            $orderCounts[] = Order::whereMonth('created_at', Carbon::today()->startOfMonth()->subMonth($i)->format('m'))->count();
        }

        return response()->json([
            'status' => 200,
            'productMonths' => $productMonths,
            'productCounts' => $productCounts,
            'orderMonths' => $orderMonths,
            'orderCounts' => $orderCounts,
            'products' => $products,
            'orders' => $orders,
            'users' => $users,
            'categories' => $categories,
            'latestOrders' => $latestOrders
        ]);
    }
}
