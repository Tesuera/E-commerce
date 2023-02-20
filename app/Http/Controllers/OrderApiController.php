<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = OrderResource::collection(Order::where('user_id', Auth::id())->latest()->get());
        if(is_null($orders)) {
            return response()->json([
                'status' => 204,
                'message' => 'No such orders is found'
            ]);
        }
        return response()->json([
            'status' => 200,
            'orders' => $orders
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:30',
            'email' => 'required|email:rfc,dns',
            'phone_number' => 'required|min:6',
            'address' => 'required|min:10',
            'user_id' => 'required',
            'payment_method' => 'required',
            'total' => 'required|integer|min:1'
        ]);

        if($request->user_id != Auth::id()) {
            return response()->json([
                'status' => 403,
                'message' => 'wrong user authentication',
                'old' => $request->all(),
            ]);
        }

        if($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'validation error',
                'errors' => $validator->errors(),
                'old' => $request->all()
            ]);
        }

        $currentOrderProduct = Cart::where('user_id', Auth::id())->get();
        if(!$currentOrderProduct->count()) {
            return response()->json([
                'status' => 204,
                'message' => 'no such cart product is found'
            ]);
        }
        $token = '#' . uniqid() . uniqid();
        $unique_id = uniqid() . '_invoice_' . uniqid();

        try {
            // store order
            $insertOrderLists = CartResource::collection($currentOrderProduct);

            $order = new Order();
            $order->unique_id = $unique_id;
            $order->token = $token;
            $order->user_id = $request->user_id;
            $order->from_date = Carbon::now();
            $order->to_date = Carbon::now()->addDays(7);
            $order->product_list = json_encode($insertOrderLists);
            $order->purchase_method = $request->payment_method;
            $order->total_amount = $request->total;
            $order->name = $request->name;
            $order->email = $request->email;
            $order->phone_number = $request->phone_number;
            $order->address = $request->address;
            $order->save();

            // decrease product stocks
            $countDecrease = $currentOrderProduct->map(function($item) {
                $product = Product::find($item->product_id);
                $left = $product->stock - $item->count;
                $product->stock = $left;
                $product->save();
            });

            Cart::destroy($currentOrderProduct->pluck('id'));

            $output = Order::find($order->id);
            return response()->json([
                'status' => 200,
                'message' => 'Order successfully. Delivered between ' . $output->from_date . ' - ' . $output->to_date,
                'order_id' => $unique_id
            ]);

        } catch (\PDOException $err) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong. Try again'
            ]);
        }
    }

    public function show($unique_id)
    {
        $order = Order::where('unique_id', $unique_id)->first();
        if(is_null($order)) {
            return response()->json([
                'status' => 204,
                'message' => 'No such order is found'
            ]);
        }
        return response()->json([
            'status' => 200,
            'order' => new OrderResource($order)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy($unique_id)
    {
        $order = Order::where('unique_id', $unique_id)->first();
        if(is_null($order)) {
            return response()->json([
                'status' => 204,
                'message' => 'no such order is found'
            ]);
        }
        $order_number = $order->id;
        $order->delete();
        return response()->json([
            'status' => 200,
            'message' => 'order number '. $order_number . ' is removed from the order list',
        ]);
    }
}
