<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $all_products = Product::all();

        return response()->json(['message' => 'success', 'data' => $all_products]);
    }
    
    public function show($id)
    {
        $product = Product::find($id);

        return response()->json(['message' => 'success', 'data' => $product]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "list_products" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        foreach($request->list_products as $product){
            Product::create([
                "name" => $product['name'],
                "stock" => $product['stock'] ? $product['stock'] : 0,
                "price" => $product['price'],
            ]);
        }

        return response()->json(['message' => 'Product created!']);
    }
}
