<?php

namespace App\Models;

use App\Interfaces\AttackHandler;
use Error;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;

class Team extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'balance',
        'reputation',
        'blue',
        'energy',
    ];

    public static function get($name){
        $team = Team::all()->where('name','=',$name)->first();
        if($team == null){
            throw new TeamNotFoundException();
        }
        return $team;
    }

    public static function getBlueTeams(){
        $teams = Team::all()->where('blue','=',1);
        if($teams->isEmpty()){
            throw new TeamNotFoundException();
        }
        return $teams;
    }

    public static function getRedTeams(){
        $teams = Team::all()->where('blue','=',0);
        if($teams->isEmpty()){
            throw new TeamNotFoundException();
        }
        return $teams;
    }

    public static function createBlueTeam($teamName){
        $team = Team::factory()->create([
            'name' => $teamName,
            'balance' => 0,
            'reputation' => 0
        ]);
        return $team;
    }

    public static function createRedTeam($teamName){
        $team = Team::factory()->red()->create([
            'name' => $teamName,
            'balance' => 0,
            'reputation' => 0
        ]);
        return $team;
    }

    public function onPreAttack($attackLog) {
        if ($this->blue == 1){
            $attackLog->blueteam_id = $this->id;
        }
        else if ($this->blue == 0){
            $attackLog->redteam_id = $this->id;
        }
        //check all assets and call onPreAttack() if possible
        $inventories = Inventory::all()->where('team_id','=', $this->id);
        foreach($inventories as $inventory){
            $asset = Asset::find($inventory->asset_id);
            $class = "\\App\\Models\\Assets\\" . $asset->name . "Asset";
            try {
                $attackHandler = new $class();
                $attackLog = $attackHandler->onPreAttack($attackLog);
            }
            catch (Error $e) {
                //Caused by specific asset model class not existing. So onPreAttack() cannot be called
                throw new AssetNotFoundException();
            }
        }
        return $attackLog;
    }

    public function leader() {
        return User::all()->where('blueteam','=',$this->id)->where('leader','=',1)->first();
    }

    public function members() {
        return User::all()->where('blueteam','=',$this->id)->where('leader','=',0);
    }

    public function assets() {
        return $this->belongstoManyThrough('App\Models\Asset', 'App\Models\Inventory');
    }

    public function inventories() {
        return Inventory::all()->where('team_id', '=', $this->id);
        //return $this->hasMany('App\Models\Inventory');
    }

    public function inventory($asset) {
        return Inventory::all()->where('team_id', '=', $this->id)->where('asset_id', '=', $asset->id)->first();
    }

    public function sellAsset($asset) {
        $inv = $this->inventory($asset);
        if ($inv == null) { return false;}
        elseif ($inv->quantity == 1){
            Inventory::destroy($inv->id);
        }
        else{
            $inv->quantity--;
            $inv->update();
        }
        $this->balance += $asset->purchase_cost;
        return $this->update();
    }

    public function buyAsset($asset) {
        if ($asset->purchase_cost > $this->balance) { return false;}

        $inv = $this->inventory($asset);
        if ($inv == null) {
            Inventory::create([
                'asset_id' => $asset->id,
                'team_id' => $this->id,
                'quantity' => 1,
            ]);
        }
        else {
            $inv->quantity++;
            $inv->update();
        }

        $this->balance -= $asset->purchase_cost;
        return $this->update();
    }

    public function setTurnTaken($turn_taken){
        if($this->blue == 0){
            throw new TeamNotFoundException();
        }
        $blueteam = Blueteam::get($this->id);
        $blueteam->setTurnTaken($turn_taken);
    }

    public function getTurnTaken(){
        if($this->blue == 0){
            throw new TeamNotFoundException();
        }
        $blueteam = Blueteam::get($this->id);
        return $blueteam->turn_taken;
    }

    public function setName($newName) {
        try{
            Team::get($newName);
        }catch(TeamNotFoundException $e){
            $this->name = $newName;
            return $this->update();
        }
        throw new TeamNotFoundException();
    }
}
