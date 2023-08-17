<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "customer_id" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $customer = Customer::find($request->customer_id);
        if($customer == null){
            return response()->json(['message' => 'Customer not found.'], 400);
        }

        // check stock
        $item_ids = array_column($request->order_items, 'product_id');
        $products = Product::whereIn('id', $item_ids)->get();

        foreach($request->order_items as $order_item){
            $product = $products->find($order_item['product_id']);

            if($product->stock < $order_item['qty']){
                return response()->json(['message' => 'Sorry, product is out of stock.'], 400);
            }
        }
        // end

        // generate order code and sequence
        $last_order = Order::whereYear('created_at', date("Y"))->whereMonth('created_at', date("M"))->orderBy('sequence', 'desc')->first();
        $last_sequence = $last_order == null ? 1 : $last_order->sequence + 1;
        $order = Order::create([
            "customer_id" => $request->customer_id,
            "sequence" => $last_sequence,
            "code" => $last_sequence . date('my'),
        ]);
        // end

        $cart_items = CartDetail::whereHas('cart', function ($query) use ($request) {
            return $query->where('customer_id', '=', $request->customer_id);
        })->get();

        foreach($request->order_items as $order_item){
            $product = $products->find($order_item['product_id']);
            $new_order = OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $order_item['product_id'],
                'qty' => $order_item['qty'],
                'price' => $product->price
            ]);

            $product->update(['stock' => $product->stock - $new_order->qty, 'updated_at' => Carbon::now()]);
            
            $cart_item = $cart_items->where('product_id', $new_order->product_id)->first();

            if($cart_item != null){
                $cart_item->update(['qty' => $cart_item->qty - $new_order->qty, 'updated_at' => Carbon::now()]);
            }
        }

        $order->update(['updated_at' => Carbon::now()]);

        return response()->json(['message' => 'Your order has created succesfully!']);
    }
}
