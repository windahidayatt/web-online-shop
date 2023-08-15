<?php

namespace App\Http\Controllers\Frontoffice;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Customer;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "customer_id" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $cart = Cart::where('customer_id', $request->customer_id)->first();

        if($cart == null){
            $cart = Cart::create([
                "customer_id" => $request->customer_id,
            ]);
        }

        $cart_items = $request->cart_items;
        $list_items = [];
        foreach($cart_items as $cart_item){
            $product = Product::find($cart_item['product_id']);
            $exist_item = $cart->cart_details->where('product_id', $cart_item['product_id'])->first();

            $item_qty = $cart_item['qty'];
            if($exist_item){
                $item_qty += $exist_item->qty;
            }

            if($product->stock < $item_qty){
                return response()->json(['message' => 'Insufficient stock.'], 400);
            }

            if($exist_item){
                $exist_item->update(['qty' => $item_qty]);
                continue;
            }

            $item = [
                'cart_id' => $cart->id,
                'product_id' => $cart_item['product_id'],
                'qty' => $cart_item['qty'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            array_push($list_items, $item);
        }

        CartDetail::insert($list_items);
        $cart->update(['updated_at' => Carbon::now()]);

        return response()->json(['message' => 'Products are added to your cart!']);
    }
}
