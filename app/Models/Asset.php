<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Exceptions\AssetNotFoundException;

class Asset //extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'class_name',
        'type',
        'blue',
        'buyable',
        'purchase_cost',
        'ownership_cost',
    ];
    protected $casts = [ 'tags' => 'array', ];

    public $_name    = "Abstract class - do not use";
    public $_class_name = "";
    public $_tags    = [];
    public $_type = 1;
    public $_blue = 1;
    public $_buyable = 1;
    public $_purchase_cost = 100;
    public $_ownership_cost = 0;

    function __construct() {
        $this->name           = $this->_name;
        $this->class_name = $this->_class_name;
        $this->type     = $this->_type;
        $this->blue = $this->_blue;
        $this->tags           = $this->_tags;
        $this->buyable      = $this->_buyable;
        $this->purchase_cost        = $this->_purchase_cost;
        $this->ownership_cost       = $this->_ownership_cost;
    }

    public function onPreAttack($attack) {}

    public static function getAll(){
        $dir = opendir(dirname(__FILE__)."/Assets");
        while(($asset = readdir($dir)) !== false){
            if($asset != "." && $asset != ".."){
                $length = strlen($asset);
                $asset = substr($asset, 0, $length - 4);
                $class = "\\App\\Models\\Assets\\" . $asset;
                $assets[] = new $class();
            }
        }
        if(count($assets) == 0){
            throw new AssetNotFoundException();
        }
        return $assets;
    }

    public static function get($name){
        $assets = Asset::getAll();
        foreach($assets as $asset){
            if($asset->class_name == $name){
                return $asset;
            }
        }
        throw new AssetNotFoundException();
    }

    public static function getByName($name){
        $assets = Asset::getAll();
        foreach($assets as $asset){
            if($asset->name == $name){
                return $asset;
            }
        }
        throw new AssetNotFoundException();
    }

    public static function getBuyableBlue(){
        $allAssets = Asset::getAll();
        foreach($allAssets as $asset){
            if($asset->buyable == 1 && $asset->blue == 1){
                $assets[] = $asset;
            }
        }
        if(count($assets ?? []) == 0){
            return null;
        }
        return $assets;
    }

    public static function getBuyableRed(){
        $allAssets = Asset::getAll();
        foreach($allAssets as $asset){
            if($asset->buyable == 1 && $asset->blue == 0){
                $assets[] = $asset;
            }
        }
        if(count($assets ?? []) == 0){
            return null;
        }
        return $assets;
    }
}
