<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function cart_details()
    {
        return $this->hasMany(CartDetail::class);
    }
    
    public function order_details()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
