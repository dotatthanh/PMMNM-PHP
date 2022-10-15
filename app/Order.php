<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
    	'code',
        'user_id',
    	'customer_id',
        'status',
        'payment_method',
    	'total_money',
    ];

    public function customer(){
    	return $this->belongsTo(Customer::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function book(){
    	return $this->belongsTo(Book::class);
    }

    public function orderDetails(){
        return $this->hasMany(OrderDetail::class);
    }
}
