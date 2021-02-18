<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class Trade extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seller_id',
        'buyer_id',
        'inv_id',
        'price',
    ];

    public static function createTrade($seller_id, $inv_id, $price){
        $trade = new Trade;
        $trade->seller_id = $seller_id;
        $trade->inv_id = $inv_id;
        $trade->price = $price;
        try{
            $trade->save();
        }catch(QueryException $e){
            return false;
        }
        return $trade;
    }

}
