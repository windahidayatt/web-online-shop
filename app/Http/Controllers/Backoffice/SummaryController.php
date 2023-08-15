<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function order_summary()
    {
        $order_items = OrderDetail::groupBy('product_id')
                        ->selectRaw('product_id, sum(qty) as qty')
                        ->get();
        dd($order_items);

        return response()->json(['message' => 'success']);
    }
}
