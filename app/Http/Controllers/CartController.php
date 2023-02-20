<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Http\Resources\CartResource;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $carts = Cart::where('user_id', Auth::id())->get()->reverse();
        $total = $carts->map(function ($item) {
            return Product::find($item->product_id)->price * $item->count;
        })->sum();
        if($carts->count()) {
            return response()->json([
                'status' => 200,
                'carts' => CartResource::collection($carts),
                'total' => $total
            ]);
        } else {
            return response()->json([
                'status' => 204,
                'message' => 'No such content is found'
            ]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'count' => 'required|integer|min:1',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ]);
        }

        if(Cart::where('product_id', $request->product_id)->where('user_id', Auth::id())->first()) {
            $currentCartProduct = Cart::where('product_id', $request->product_id)->first();
            $product = Product::find($request->product_id);

            $total = $currentCartProduct->count + $request->count;
            if($total > $product->stock) {
                return response()->json([
                    'status' => 400,
                    'message' =>  'Limited amount of stock',
                ]);
            }
            $currentCartProduct->count = $total;
            $currentCartProduct->save();
            return response()->json([
                'status' => 200,
                'message' => $product->name . ' is added to the cart.',
                'cart' => $currentCartProduct
            ]);
        } else {
            $product = Product::find($request->product_id);
            if($request->count > $product->stock) {
                return response()->json([
                    'status' => 400,
                    'message' =>  'Limited amount of stock',
                ]);
            }
            $newcart = Cart::create([
                'product_id' => $request->product_id,
                'user_id' => Auth::id(),
                'count' => $request->count
            ]);

            return response()->json([
                'status' => 200,
                'message' => $product->name . ' is added to the cart.',
                'cart' => $newcart
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $currentCart = Cart::find($id);
        if(is_null($currentCart)) {
            return response()->json([
                'status' => 204,
                'message' => 'No such content is found'
            ]);
        }
        $currentCart->count = $request->count;
        $currentCart->update();

        return response()->json([
            'status' => 200,
            'message' => 'updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $cart = Cart::find($id);
        if(is_null($cart)) {
            return response()->json([
                'status' => 204,
                'message' => 'No such content is found'
            ]);
        }
        $cart->delete();
        return response()->json([
            'status' => 200,
            'message' => 'deleted successfully'
        ]);
    }
}
