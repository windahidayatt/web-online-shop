<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function order_summary()
    {
        $orders = Order::all();

        $sum_total = 0;
        $completed_orders = 0;
        $uncompleted_orders = 0;

        foreach($orders as $order){
            foreach($order->order_details as $order_detail){
                $sum_total += $order_detail->qty * $order_detail->price;
            };
            if($order->status == 1){
                $completed_orders++;
            }else{
                $uncompleted_orders++;
            }
        }

        $res = [
            'orders' => count($orders),
            'totals' => $sum_total,
            'completed_orders' => $completed_orders,
            'uncompleted_orders' => $uncompleted_orders,
        ];

        return response()->json(['message' => 'success', 'data' => $res]);
    }

    public function product_summary()
    {
        $products = Product::all();

        $in_stock = $products->where('stock', '>', '0')->count();
        $out_stock = $products->where('stock', '<=', '0')->count();

        $order_item = OrderDetail::groupBy('product_id')
                        ->selectRaw('product_id, sum(qty) as qty')
                        ->orderBy('qty', 'desc')
                        ->first();

        $most_ordered = [];

        if($order_item != null){
            $most_ordered_product = Product::find($order_item->product_id);

            $most_ordered = [
                'product_id' => $order_item->product_id,
                'product_name' => $most_ordered_product->name,
                'qty' => $order_item->qty
            ];
        }
        
        $res = [
            'products' => count($products),
            'in_stock' => $in_stock,
            'out_stock' => $out_stock,
            'most_ordered' => $most_ordered,
        ];

        return response()->json(['message' => 'success', 'data' => $res]);
    }

    public function order_per_product()
    {
        $order_items = OrderDetail::groupBy('product_id')
                        ->selectRaw('product_id, sum(qty) as qty')
                        ->get();
        $res = [];

        foreach($order_items as $order_item){
            $product = Product::find($order_item->product_id);
            array_push($res, [
                'product_id' => $order_item->product_id,
                'product_name' => $product->name,
                'qty' => $order_item->qty
            ]);
        }

        return response()->json(['message' => 'success', 'data' => $res]);
    }
}
