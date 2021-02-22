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
        if($price < 0)
            return false;
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

    public static function getByInv($inv_id){
        $trade = Trade::all()->where('inv_id', '=', $inv_id);
        if($trade->isEmpty())
            return false;
        return $trade;
    }

    public static function removeByInv($inv_id){
        $trades = Trade::getByInv($inv_id);
        if($trades == false)
            return false;
        foreach($trades as $trade){
            Trade::destroy($trade->id);
        }
        return true;
    }

    public static function getCurrentBlueTrades(){
        $trades = Trade::all()->where('buyer_id','=',null);
        $blueTrades = [];
        foreach($trades as $trade){
            if(Team::find($trade->seller_id)->blue == 1){
                $blueTrades[] = $trade;
            }
        }
        return collect($blueTrades);
    }

    public static function getCurrentRedTrades(){
        $trades = Trade::all()->where('buyer_id','=',null);
        $blueTrades = [];
        foreach($trades as $trade){
            if(Team::find($trade->seller_id)->blue == 0){
                $blueTrades[] = $trade;
            }
        }
        return collect($blueTrades);
    }

}
