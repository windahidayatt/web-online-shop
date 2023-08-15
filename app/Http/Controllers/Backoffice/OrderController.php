<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $all_orders = Order::with('order_details', 'customer')->get();

        return response()->json(['message' => 'success', 'data' => $all_orders]);
    }
}
