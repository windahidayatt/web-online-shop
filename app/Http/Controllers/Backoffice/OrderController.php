<?php

namespace App\Http\Controllers\Backoffice;

use App\Exports\OrderExport;
use App\Http\Controllers\Controller;
use App\Imports\OrderImport;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $all_orders = Order::with('order_details', 'order_details.product', 'customer');

        if($request->has('search_product')){
            $all_orders = $all_orders->whereHas('order_details.product', function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->query('search_product') . '%');
            });
        }

        if($request->has('order_year')){
            $all_orders = $all_orders->whereYear('created_at', $request->query('order_year'));
        }

        if($request->has('max_sequence')){
            $all_orders = $all_orders->where('sequence', '<=', $request->query('max_sequence'));
        }

        return response()->json(['message' => 'success', 'data' => $all_orders->get()]);
    }

    public function import(Request $request) 
    {   
        $import_data = Excel::import(new OrderImport, $request->file('file_import'));
        if($import_data){
            return response()->json(['message' => 'success']);
        }else{
            return response()->json(['message' => 'fail']);
        }
    }

    public function export()
    {
        date_default_timezone_set('Asia/Jakarta');
        $time_now = date('d M Y', time()) . ' WIB';

        return Excel::download(new OrderExport, "Order Recap - {$time_now}.xlsx");
    }

    public function update_status($id) 
    {   
        $update_data = Order::find($id)->update(['is_complete' => 1]);

        if($update_data){
            return response()->json(['message' => 'Order status has been updated!']);
        }else{
            return response()->json(['message' => 'fail']);
        }
    }
}
