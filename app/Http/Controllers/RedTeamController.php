<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Inventory;
use App\Models\Prereq;
use App\Models\Attack;
use Auth;
use View;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Exceptions\InventoryNotFoundException;

class RedTeamController extends Controller {

    public function page($page, Request $request) {
        $redteam = Team::find(Auth::user()->redteam);
        if($redteam == null && $page != 'create'){
            return $this->home(); 
        }
        switch ($page) {
            case 'home': return $this->home(); break;
            case 'attacks': return $this->attacks(); break;
            case 'learn': return view('redteam.learn')->with('redteam',$redteam); break;
            case 'store': return $this->store();break;
            case 'status': return view('redteam.status')->with('redteam',$redteam); break;
            case 'create': return $this->create($request); break;
            case 'buy': return $this->buy($request); break;
            case 'storeinventory': return $this->storeInventory(); break;
            case 'sell': return $this->sell($request); break;
            case 'startattack': return $this->startAttack(); break;
            case 'chooseattack': return $this->chooseAttack($request); break;
            case 'performattack': return $this->performAttack($request); break;
            case 'attackhandler': return $this->attackHandler($request); break;
            case 'settings': return $this->settings($request); break;
            case 'changename': return $this->changeName($request); break;
            case 'leaveteam': return $this->leaveTeam($request); break;
            default: return $this->home(); break;
        }
    }

    public function leaveTeam(request $request){
        if($request->result == "stay"){
            return $this->settings($request);
        }
        else if($request->result != "leave"){
            $error = "invalid-choice";
            return $this->settings($request)->with(compact('error'));
        }
        $user = Auth::user();
        $teamID = $user->redteam;
        $user->redteam = null;
        $user->update();
        $team = Team::find($teamID);
        if($team == null){
            throw new TeamNotFoundException();
        }
        Team::destroy($teamID);
        return $this->home();
    }

    public function changeName(request $request){
        if(!Team::all()->where('name','=',$request->name)->isEmpty()){
            $error = "name-taken";
            return $this->settings($request)->with(compact('error'));
        }
        $teamID = Auth::user()->redteam;
        $team = Team::find($teamID);
        $team->name = $request->name;
        $team->update();
        $newTeam = Team::find($teamID);
        if($newTeam->name == $request->name){
            return $this->settings($request);
        }else{
            throw new Exception("Name unchanged");
        }
    }

    public function settings($request){
        $changeName = false;
        $leaveTeam = false;
        if($request->changeNameBtn == 1){
            $changeName = true;
        }
        if($request->leaveTeamBtn == 1){
            $leaveTeam = true;
        }
        $teamID = Auth::user()->redteam;
        $redteam = Team::find($teamID);
        if($redteam == null){
            throw new TeamNotFoundException();
        }
        return view('redteam/settings')->with(compact('redteam','changeName','leaveTeam'));
    }

    public function home(){
        $redteam = Team::find(Auth::user()->redteam);
        return view('redteam.home')->with('redteam',$redteam);
    }

    public function attackHandler(request $request){
        if($request->result == ""){
            $error = "No-Attack-Selected";
            return $this->chooseAttack($request)->with(compact('error'));
        }
        $redteam = Team::find(Auth::user()->redteam);
        $blueteam = Team::all()->where('name','=',$request->blueteam)->first();
        //TEST HANDLER UNTIL WE KNOW MORE ABOUT ATTACKS
        if($request->result == "success"){
            $redteam->balance += 1000;
            $redteam->update();
            $attMsg = "Success in Attacking";
        }else{
            $attMsg = "Failure in Attacking";
        }
        return $this->home()->with(compact('attMsg'));
    }

    public function attacks(){
        $possibleAttacks = Attack::all();
        $redteam = Team::find(Auth::user()->redteam);
        return view('redteam.attacks')->with(compact('redteam','possibleAttacks')); 
    }

    public function performAttack(request $request){
        if($request->result == ""){
            $error = "No-Attack-Selected";
            return $this->chooseAttack($request)->with(compact('error'));
        }
        $user = Auth::user();
        $redteam = Team::find(Auth::user()->redteam);
        $blueteam = Team::all()->where('name','=',$request->blueteam)->first();
        $attack = Attack::all()->where('name', '=', $request->result);
        if($attack->isEmpty()){ throw new AssetNotFoundException();}
        $attack = $attack->first();
        return view('redteam.performAttack')->with(compact('redteam','blueteam','attack'));
    }

    public function chooseAttack(request $request){
        if($request->result == ""){
            $error = "No-Team-Selected";
            return $this->startAttack()->with(compact('error'));
        }
        $user = Auth::user();
        $redteam = Team::find(Auth::user()->redteam);
        $blueteam = Team::all()->where('name', '=', $request->result);
        if($blueteam->isEmpty()){ throw new TeamNotFoundException();}
        $blueteam = $blueteam->first();
        $targetAssets = Inventory::all()->where('team_id','=', $blueteam);
        $notPossibleBlueAttackIDs = Prereq::all()->whereIn('asset_id',$targetAssets->pluck('id')); //attackIDs you can do against blue
        $possibleAttacks = Attack::all();
        $uselessPossibleAttacks = $possibleAttacks->whereIn('id', $notPossibleBlueAttackIDs->pluck('id'));
        $possibleAttacks = $possibleAttacks->whereNotIn('id',$notPossibleBlueAttackIDs->pluck('id')); //only attacks you can do against blue
        return view('redteam.chooseAttack')->with(compact('redteam','blueteam','possibleAttacks','uselessPossibleAttacks'));
    }

    public function startAttack(){
        $targets = Team::all()->where('blue','=','1');
        $redteam = Team::find(Auth::user()->redteam);
        return view('redteam.startAttack')->with(compact('targets','redteam'));
    }

    public function sell(request $request){
        //change this to proportion sell rate
        $sellRate = 1;
        $assetNames = $request->input('results');
        $assets = Asset::all()->where('blue', '=', 0)->where('buyable', '=', 1);
        $redteam = Team::find(Auth::user()->redteam);
        if($redteam == null){
            throw new TeamNotFoundException();
        }
        if($assetNames == null){
            $error = "no-asset-selected";
            return view('redteam.store')->with(compact('assets','error', 'redteam'));
        }
        foreach($assetNames as $assetName){
            //remove asset from inventory and pay team
            $asset = Asset::all()->where('name','=',$assetName)->first();
            if ($asset == null){
                throw new AssetNotFoundException();
            }
            $currInventory = Inventory::all()->where('team_id','=',$redteam->id)->where('asset_id','=', $asset->id)->first();
            if($currInventory == null){
                throw new InventoryNotFoundException();
            }
            $currInventory->quantity -= 1;
            if($currInventory->quantity == 0){
                Inventory::destroy(substr($currInventory->pluck('id'),1,1));
            }else{
                $currInventory->update();
            }
            $redteam->balance += ($asset->purchase_cost)*$sellRate;
            $redteam->update();
        }
        return view('redteam.store')->with(compact('redteam', 'assets'));
    }//end sell

    public function storeInventory(){
        $redteam = Team::find(Auth::user()->redteam);
        $inventory = Inventory::all()->where('team_id','=', Auth::user()->redteam);
        $assets = Asset::all()->where('blue', '=', 0)->where('buyable', '=', 1);
        return view('redteam.store')->with(compact('redteam', 'assets', 'inventory'));
    }

    public function buy(request $request){
        $assetNames = $request->input('results');
        $assets = Asset::all()->where('blue', '=', 0)->where('buyable', '=', 1);
        $redteam = Team::find(Auth::user()->redteam);
        if($redteam == null){
            throw new TeamNotFoundException();
        }
        if($assetNames == null){
            $error = "no-asset-selected";
            return view('redteam.store')->with(compact('assets','error','redteam'));
        }
        $totalCost = 0;
        foreach($assetNames as $assetName){
            $asset = Asset::all()->where('name','=',$assetName)->first();
            if($asset == null){
                throw new AssetNotFoundException();
            }
            $totalCost += $asset->purchase_cost;
        }
        if($redteam->balance < $totalCost){
            $error = "not-enough-money";
            return view('redteam.store')->with(compact('assets','error','redteam'));
        }
        foreach($assetNames as $asset){
            //add asset to inventory and charge team
            $assetId = substr(Asset::all()->where('name','=',$asset)->pluck('id'), 1, 1);
            $currAsset = Inventory::all()->where('team_id','=',Auth::user()->redteam)->where('asset_id','=', $assetId)->first();
            if($currAsset == null){
                Inventory::factory()->create([
                    'team_id' => $redteam,
                    'asset_id' => $assetId,
                    'quantity' => 1,
                ]);
            }else{
                $currAsset->quantity += 1;
                $currAsset->update();
            }
        }
        $redteam->balance -= $totalCost;
        $redteam->update();
        return view('redteam.store')->with(compact('redteam', 'assets'));
    }

    public function store(){
        $redteam = Team::find(Auth::user()->redteam);
        $assets = Asset::all()->where('blue', '=', 0)->where('buyable', '=', 1);
        //$prereqs = Prereq::all()->whereIn('asset_id',$assets->pluck('id'));
        return view('redteam.store')->with(compact('redteam', 'assets'));
    }

    public function create(request $request){
        if($request->name == ""){ return view('redteam.create');} 
        $request->validate([
            'name' => ['required', 'unique:teams', 'string', 'max:255'],
        ]);
        $redteam = new Team();
        $redteam->name = $request->name;
        $redteam->balance = 0;
        $redteam->blue = 0;
        $redteam->reputation = 0;
        $redteam->save();
        $user = Auth::user();
        $user->redteam = substr(Team::all()->where('name', '=', $request->name)->pluck('id'), 1, 1);
        $user->update();
        return view('redteam.home')->with('redteam',$redteam);
    }

    public function delete(request $request){
        $team = Team::all()->where('name', '=', $request->name);
        if($team->isEmpty()) {
            throw new TeamNotFoundException();
        }
        $id = substr($team->pluck('id'), 1, 1);
        Team::destroy($id);
        return view('home');
    }
}
