<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    protected $table = 'customers';

    protected $fillable = [
    	'name',
    	'email',
    	'phone',
    	'address',
        'password',
        'code',
        'birthday',
        'sex',
    ];

    public function orders(){
    	return $this->hasMany(Order::class);
    }

    public function type(){
    	return $this->belongTo(Type::class);
    }
}
