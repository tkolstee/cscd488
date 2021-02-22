<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\InventoryNotFoundException;
use App\Models\Team;
use App\Models\Asset;


class Inventory extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quantity',
        'team_id',
        'asset_name',
        'level',
        'info',
    ];

    public function reduce(){
        if($this == null) throw new InventoryNotFoundException();
        if($this->quantity == 1){
            Trade::removeByInv($this->id);
            Inventory::destroy($this->id);
        }else{
            $this->quantity--;
            $this->update();
            $this->removeTrade();
        }
    }

    public function usedToken(){
        if($this->asset_name != "AccessToken" && $this->asset_name != "Insider") throw new InventoryNotFoundException();
        $rand = rand(1,4);
        if ($rand > 1){
            $this->reduce();
        }
    }

    public function removeTrade(){
        $trade = Trade::getByInv($this->id);
        if($trade != false){
            if($this->quantity == 1 || $this->quantity <= count($trade)){
                return Trade::removeByInv($this->id);
            }
        }
        return false;
    }

    public function setInfo($string){
        $team = Team::find($this->team_id);
        $this->removeTrade();
        $inv = $team->inventoryWithInfo($this->asset_name,$this->level,$string);
        if($inv == null){
            if($this->quantity == 1){
                $this->info = $string;
            }else{
                $inv = new Inventory();
                $inv->asset_name = $this->asset_name;
                $inv->team_id = $this->team_id;
                $inv->level = $this->level;
                $inv->info = $string;
                $inv->save();
                $this->quantity--;
            }
        }else{
            $inv->quantity++;
            $inv->update();
            if($this->quantity == 1)
                $this->destroy($this->id);
            else{
                $this->quantity--;
            }
        }
        $this->update();
    }

    public function getUpgradeCost(){
        $asset = Asset::get($this->asset_name);
        if($asset == null) throw new AssetNotFoundException();
        return $asset->upgrade_cost * $this->level;
    }

    public function upgrade(){
        $this->removeTrade();
        if($this->level == 3){
            return false;
        }
        if($this->quantity == 0){
            throw new InventoryNotFoundException();
        }
        $cost = $this->getUpgradeCost();
        $team = Team::find($this->team_id);
        if($team->balance < $cost) return false;
        $team->balance -= $cost;
        $team->update();
        $asset_name = $this->asset_name;
        $level = $this->level;
        if($this->quantity == 1){
            $this->destroy($this->id);
        }
        else{
            $this->quantity--;
            $this->update();
        }
        $asset = Asset::get($asset_name);
        $inv = $team->inventory($asset, $level + 1);
        if($inv == null){
            $inv = new Inventory();
            $inv->quantity = 1;
            $inv->team_id = $this->team_id;
            $inv->asset_name = $this->asset_name;
            $inv->level = $this->level + 1;
            $inv->save();
        }else{
            $inv->quantity++;
            $inv->update();
        }
        return true;
    }

    public function getAssetName(){
        if ($this->asset_name != 'AccessToken') { 
            return Asset::get($this->asset_name)->name;
        }
        else {
            if ($this->level == 1) { return "Basic Access";}
            elseif ($this->level == 2) { return "Private Access";}
            elseif ($this->level ==3 ) { return "Pwnd Access";}
        }
    }
}
