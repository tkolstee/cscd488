<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\AssetNotFoundException;

class Asset extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'blue',
        'buyable',
        'purchase_cost',
        'ownership_cost',
    ];

    public static function get($name){
        $asset = Asset::all()->where('name','=',$name)->first();
        if($asset == null){
            throw new AssetNotFoundException();
        }
        return $asset;
    }

    public static function getBuyableBlue(){
        $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
        if($assets->isEmpty()){
            throw new AssetNotFoundException();
        }
        return $assets;
    }

    public static function getBuyableRed(){
        $assets = Asset::all()->where('blue', '=', 0)->where('buyable', '=', 1);
        if($assets->isEmpty()){
            throw new AssetNotFoundException();
        }
        return $assets;
    }
}
