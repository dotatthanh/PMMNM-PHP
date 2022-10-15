<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReturnOrderDetail extends Model
{
    protected $fillable = [
    	'return_order_id',
    	'book_id',
    	'amount',
    	'price',
        'sale',
        'total_money',
        'discount',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function returnOrder()
    {
        return $this->belongsTo(ReturnOrder::class);
    }
}
