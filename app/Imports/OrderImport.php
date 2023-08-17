<?php

namespace App\Imports;

use App\Models\CartDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class OrderImport implements ToCollection, WithStartRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $new_order = new Order();

        define("COL_NO", 0);
        define("COL_CUST_ID", 1);
        define("COL_PRODUCT_ID", 2);
        define("COL_QTY", 3);

        foreach ($collection as $row) {
            $product = Product::find($row[COL_PRODUCT_ID]);

            if($product->stock < $row[COL_QTY]){
                return response()->json(['message' => 'Sorry, product is out of stock.'], 400);
            }
        }

        $last_order_code = "";
        foreach($collection as $row){
            
            $cart_items = CartDetail::whereHas('cart', function ($query) use ($row){
                return $query->where('customer_id', '=', $row[COL_CUST_ID]);
            })->get();

            if($row[COL_NO] != ""){
                $product = Product::find($row[COL_PRODUCT_ID]);

                $last_order = Order::whereYear('created_at', date("Y"))->whereMonth('created_at', date("m"))->orderBy('sequence', 'desc')->first();
                $last_sequence = $last_order == null ? 1 : $last_order->sequence + 1;

                $new_order = Order::create([
                    "customer_id" => $row[COL_CUST_ID],
                    "sequence" => $last_sequence,
                    "code" => $last_sequence . date('my')
                ]);

                OrderDetail::create([
                    'order_id' => $new_order->id,
                    'product_id' => $row[COL_PRODUCT_ID],
                    'qty' => $row[COL_QTY],
                    'price' => $product->price
                ]);

                $last_order_code = $new_order->code;
            }else{
                $order = Order::where('code', $last_order_code)->first();

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $row[COL_PRODUCT_ID],
                    'qty' => $row[COL_QTY],
                    'price' => $product->price
                ]);
            }

            $product->update(['stock' => $product->stock - $row[COL_QTY], 'updated_at' => Carbon::now()]);
            
            $cart_item = $cart_items->where('product_id', $row[COL_PRODUCT_ID])->first();

            if($cart_item != null){
                $updated_qty_cart_item = $cart_item->qty - $row[COL_QTY];
                $cart_item->update(['qty' => $updated_qty_cart_item < 0 ? 0 : $updated_qty_cart_item, 'updated_at' => Carbon::now()]);
            }
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}
