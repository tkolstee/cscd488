<?php

namespace App\Models;

use Error;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Exceptions\InventoryNotFoundException;
use App\Models\Blueteam;
use App\Models\Asset;
use App\Models\Bonus;

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

    public function getPerTurnRevenue(){
        if($this->blue != 1) throw new TeamNotFoundException();
        $revGained = 0;
        $revBonus = 0;
        $inventories = $this->inventories();
        foreach($inventories as $inv){
            $asset = Asset::get($inv->asset_name);
            $revGained -= $asset->ownership_cost;
            $revBonus += $asset->percentRevBonus;
        }
        return $revGained * ($revBonus*0.01 + 1);
    }

    public function getBonuses(){
        $bonuses = Bonus::all()->where('team_id','=',$this->id);
        return $bonuses;
    }

    public function getBonusesByTarget(){
        $bonuses = Bonus::all()->where('target_id','=',$this->id);
        return $bonuses;
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
        return Inventory::all()->where('team_id', '=', $this->id)->where('asset_name', '=', $asset->class_name)->where('level', '=', $level)->where('info','=',null)->first();
    }

    public function inventoryWithInfo($assetName, $level, $info){
        return Inventory::all()->where('team_id', '=', $this->id)->where('asset_name', '=', $assetName)->where('level', '=', $level)->where('info' ,'=', $info)->first();
    }

    public function createTrade($inv_id, $price){
        $inv = Inventory::find($inv_id);
        if($inv == null)
            throw new InventoryNotFoundException();
        if($inv->team_id != $this->id)
            throw new InventoryNotFoundException();
        $asset = Asset::get($inv->asset_name);
        if($asset->buyable == 0)
            return false;
        if(in_array("Targeted", $asset->tags) && $inv->info != null){
            return false;
        }
        if($price < 0) return false;
        $trade = Trade::createTrade($this->id, $inv_id, $price);
        if($trade != false)
            return $trade;
        else   
            return false;
    }

    public function getCurrentTrades(){
        $trades = Trade::all()->where('seller_id','=',$this->id)->where('buyer_id','=',null);
        return $trades;
    }

    public function getBoughtTrades(){
        $trades = Trade::all()->where('buyer_id','=',$this->id);
        return $trades;
    }

    public function getSoldTrades(){
        $trades = Trade::all()->where('seller_id','=',$this->id)->where('buyer_id','<>',null);
        return $trades;
    }

    public function tradeableInventories(){
        $invs = $this->inventories();
        $tradeable = [];
        foreach($invs as $inv){
            $asset = Asset::get($inv->asset_name);
            if($asset->buyable == 1){
                $trade = Trade::getByInv($inv->id);
                if($trade != false && count($trade) < $inv->quantity){
                    $tradeable[] = $inv;
                }else if($trade == false){
                    $tradeable[] = $inv;
                }
            }
        }
        return $tradeable;
    }

    public function completeTrade($trade_id){
        $trade = Trade::find($trade_id);
        if($trade == null || $trade->buyer_id != null)
            throw new InventoryNotFoundException;
        if($this->balance < $trade->price)
            return false;
        $seller = Team::find($trade->seller_id);
        if($seller == null || $seller->blue != $this->blue)
            throw new TeamNotFoundException;
        $sellInv = Inventory::find($trade->inv_id);
        if($sellInv == null || $sellInv->team_id != $seller->id)
            throw new InventoryNotFoundException;
        $asset = Asset::get($sellInv->asset_name);
        if($sellInv->info == null){
            $buyInv = $this->inventory($asset, $sellInv->level);
        }elseif(in_array("Targeted", $asset->tags)){
            throw new InventoryNotFoundException;
        }else{
            $buyInv = $this->inventoryWithInfo($asset->class_name, $sellInv->level, $sellInv->info);
        }
        if($buyInv == null){
            $buyInv = new Inventory();
            $buyInv->asset_name = $sellInv->asset_name;
            $buyInv->team_id = $this->id;
            $buyInv->level = $sellInv->level;
            $buyInv->quantity = 1;
            $buyInv->info = $sellInv->info;
            $buyInv->save();
        }else{
            $buyInv->quantity++;
            $buyInv->update();
        }
        $sellInv->quantity--;
        if($sellInv->quantity == 0){
            Inventory::destroy($sellInv->id);
        }else{
            $sellInv->update();
        }
        $this->balance -= $trade->price;
        $this->update();
        $seller->balance += $trade->price;
        $seller->update();
        $trade->buyer_id = $this->id;
        $trade->update();
        return $trade;
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
        $inventories = $this->inventories();
        foreach($inventories as $inv){
            $asset = Asset::get($inv->asset_name);
            if($asset->hasTag('TurnConsumable')){
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

    public function getTokenQuantity($info, $level){
        if($this->blue != 1) {
            $inv = Inventory::all()->where('team_id', '=', $this->id)->where('asset_name', '=', 'AccessToken')
            ->where('info', '=', $info)->where('level', '=', $level)->first();
            
            if ($inv == null) { return 0;}
            return $inv->quantity;
        }
    }

    public function getTokensByBlue(){
        if($this->blue == 0) throw new TeamNotFoundException();
        $invs = Inventory::all()->where('info', '=', $this->name)->where('asset_name','=',"AccessToken");
        return $invs;
    }

    public function changeBalance($balChange){
        $this->balance += $balChange;
        if ($this->balance < 0){
            $this->balance = 0;
        }
        $this->balance = round( $this->balance , 0 , $mode = PHP_ROUND_HALF_UP );
        $this->update();
    }

    public function changeReputation($repChange){
        $this->reputation += $repChange;
        if ($this->reputation < 0){
            $this->reputation = 0;
        }
        $this->update();
    }

    public function addBonusReputation() {
        $dayStreak = $this->daysSinceLastAttack();
        if ($dayStreak <= 0) {
            return;
        }
        elseif ($dayStreak >= 7) {
            $this->changeReputation(3200);
        }
        else {
            $rep = 50*pow(2, $dayStreak-1);
            $this->changeReputation($rep);
        }
    }

    public function daysSinceLastAttack() {
        $recentAttack = Attack::all()->where('blueteam', '=', $this->id)->where('success','=', true)->sortBy('created_at')->first();
        if ($recentAttack == null) {
            return $this->created_at->diffInDays();
        }
        return $recentAttack->created_at->diffInDays();
    }

    public function sellInventory($inv) {
        if ($inv == null) { return false;}
        if ($inv->team_id != $this->id) { return false; }
        $inv->removeTrade();
        $asset = Asset::get($inv->asset_name);
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

    public function collectAssetTags($attack) {
        $inventories = $this->inventories();
        $tags = [];
        foreach ($inventories as $invAsset){
            $asset = Asset::get($invAsset->asset_name);
            if (in_array('Targeted', $asset->tags)){
                if (isValidTargetedAsset($asset,$invAsset->info, $attack)){
                    $tags[] = $invAsset->asset_name;
                    if($invAsset->asset_name == "AccessToken"){
                        if ($invAsset->level == 1) {
                            $tags[] = "BasicAccess";
                        }
                        else if ($invAsset->level == 2) {
                            $tags[] = "PrivilegedAccess";
                        }
                        else if ($invAsset->level == 3) {
                            $tags[] = "PwnedAccess";
                        }
                    }
                    else {
                        $tags = array_merge($tags, $asset->tags);
                    }
                }
            }
            else {
                $tags[] = $invAsset->asset_name;
                $tags = array_merge($tags, $asset->tags);
            }
        }
        return $tags;
    }
}
