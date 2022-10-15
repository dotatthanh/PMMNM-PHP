<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReturnOrder extends Model
{
    protected $fillable = [
    	'code',
        'order_id',
    	'customer_id',
        'user_id',
        'total_money',
        'status',
        'reason',
    ];

    public function customer(){
    	return $this->belongsTo(Customer::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function order(){
    	return $this->belongsTo(Order::class);
    }

    public function returnOrderDetails(){
        return $this->hasMany(ReturnOrderDetail::class);
    }
}
