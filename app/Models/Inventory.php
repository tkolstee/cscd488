<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\AssetNotFoundException;
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
    ];

    public function getUpgradeCost(){
        $asset = Asset::get($this->asset_name);
        if($asset == null) throw new AssetNotFoundException();
        return $asset->upgrade_cost * $this->level;
    }

    public function upgrade(){
        if($this->level == 3){
            return false;
        }
        if($this->quantity == 0){
            throw new AssetNotFoundException();
        }
        $cost = $this->getUpgradeCost();
        $team = Team::find($this->team_id);
        if($team->balance < $cost) return false;
        $team->balance -= $cost;
        $team->update();
        if($this->quantity == 1){
            $this->level++;
            $this->update();
        }
        else{
            $this->quantity--;
            $this->update;
            $inv = new Inventory();
            $inv->quantity = 1;
            $inv->team_id = $this->team_id;
            $inv->asset_name = $this->asset_name;
            $inv->level = $this->level + 1;
            $inv->save();
        }
        return true;
    }
}
