<?php

namespace App\Models;

use Error;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Exceptions\InventoryNotFoundException;
use App\Models\Blueteam;

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
        Blueteam::create($team->id);
        return $team;
    }

    public static function createRedTeam($teamName){
        $team = Team::factory()->red()->create([
            'name' => $teamName,
            'balance' => 0,
            'reputation' => 0
        ]);
        Redteam::create($team->id);
        return $team;
    }

    public function leader() {
        return User::all()->where('blueteam','=',$this->id)->where('leader','=',1)->first();
    }

    public function members() {
        return User::all()->where('blueteam','=',$this->id)->where('leader','=',0);
    }

    public function inventories() {
        return Inventory::all()->where('team_id', '=', $this->id);
        //return $this->hasMany('App\Models\Inventory');
    }

    public function inventory($asset, $level) {
        return Inventory::all()->where('team_id', '=', $this->id)->where('asset_name', '=', $asset->class_name)->where('level', '=', $level)->first();
    }

    public function assets() {
        $inventories = Inventory::all()->where('team_id', '=', $this->id);
        $assets_arr = [];
        foreach ($inventories as $inventory){
            $assets_arr[] = Asset::get($inventory->asset_name);
        }
        return collect($assets_arr);
    }

    public function useTurnConsumables(){
        if($this->blue != 1) throw new TeamNotFoundException();
        $inventories = $this->inventories();
        foreach($inventories as $inv){
            $asset = Asset::get($inv->asset_name);
            if(in_array($asset->tags, 'TurnConsumable')){
                $inv->reduce();
            }
        }
    }

    public function addToken($info, $level){
        if($this->blue == 1) throw new TeamNotFoundException();
        $token = Inventory::all()->where('team_id', '=', $this->id)->where('asset_name','=',"AccessToken")->
            where('info', '=', $info)->where('level','=',$level)->first();
        if($token == null) {
            $token = new Inventory();
            $token->team_id = $this->id;
            $token->asset_name = "AccessToken";
            $token->level = $level;
            $token->info = $info;
            $token->save();
        }
        else{
            $token->quantity++;
            $token->update();
        }
    }

    public function removeToken($info, $level){
        if($this->blue == 1) throw new TeamNotFoundException();
        $token = Inventory::all()->where('team_id', '=', $this->id)->where('asset_name','=',"AccessToken")->
            where('info', '=', $info)->where('level','=',$level)->first();
        $token->reduce();
    }

    public function getTokens(){
        if($this->blue == 1) throw new TeamNotFoundException();
        $inventories = Inventory::all()->where('team_id', '=', $this->id)->where('asset_name','=',"AccessToken");
        return $inventories;
    }

    public function changeBalance($balChange){
        $this->balance += $balChange;
        if ($this->balance < 0){
            $this->balance = 0;
        }
        $this->update();
    }

    public function changeReputation($repChange){
        $this->reputation += $repChange;
        if ($this->reputation < 0){
            $this->reputation = 0;
        }
        $this->update();
    }

    public function sellAsset($asset, $level) {
        $inv = $this->inventory($asset, $level);
        if ($inv == null) { return false;}
        //get last upgrade cost
        $inv->level--;
        $lastUpCost = $inv->getUpgradeCost();
        $inv->level++;
        $inv->reduce();
        $this->balance += $asset->purchase_cost + $lastUpCost;
        return $this->update();
    }

    public function buyAsset($asset) {
        if ($asset->purchase_cost > $this->balance) { return false;}
        $inv = $this->inventory($asset, 1);
        if ($inv == null) {
            Inventory::create([
                'asset_name' => $asset->class_name,
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
        $turn = $blueteam->turn_taken;
        if($turn == null) $turn = 0;
        return $turn;
    }

    public function getEnergy(){
        if($this->blue == 1){
            throw new TeamNotFoundException();
        }
        $redteam = Redteam::get($this->id);
        $energy = $redteam->energy;
        if($energy == null) $energy = 0;
        return $energy;
    }

    public function setEnergy($energy){
        if($this->blue == 1){
            throw new TeamNotFoundException();
        }
        $redteam = Redteam::get($this->id);
        $redteam->setEnergy($energy);
    }

    public function useEnergy($energyCost){
        if($this->blue == 1){
            throw new TeamNotFoundException();
        }
        $redteam = Redteam::get($this->id);
        $redteam->useEnergy($energyCost);
    }

    public function setName($newName) {
        try{
            Team::get($newName);
        }catch(TeamNotFoundException $e){
            $this->name = $newName;
            return $this->update();
        }
        return false;
    }

    public function hasAnalyst() {
        $assets = $this->assets();
        foreach ($assets as $asset) {
            if (in_array('Analysis', $asset->tags)) { return true;}
        }
        return false;
    }
}
