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
use Exception;

class RedTeamController extends Controller {

    public function page($page, Request $request) {
        $redteam = Team::find(Auth::user()->redteam);
        switch ($page) {
            case 'home': return view('redteam.home')->with('redteam',$redteam); break;
            case 'attacks': return view('redteam.attacks')->with('redteam',$redteam); break;
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
        }
    }

    public function performAttack(request $request){
        if($request->result == ""){
            $error = "No-Attack-Selected";
            return $this->chooseAttack($request)->with(compact('error'));
        }
        $user = Auth::user();
        $redteam = Team::find(Auth::user()->redteam);
        $asset = Asset::all()->where('name', '=', $request->asset);
        if($asset->isEmpty()) throw new Exception("AssetDoesNotExist");
        //Continue Working Here
    }

    public function chooseAttack(request $request){
        if($request->result == ""){
            $error = "No-Team-Selected";
            return $this->startAttack()->with(compact('error'));
        }
        $user = Auth::user();
        $redteam = Team::find(Auth::user()->redteam);
        $blueteam = Team::all()->where('name', '=', $request->result);
        if($blueteam->isEmpty()) throw new Exception("TeamDoesNotExist");
        $blueteam = $blueteam->first();
        $assets = Inventory::all()->where('team_id','=', Auth::user()->redteam);
        $targetAssets = Inventory::all()->where('team_id','=', $blueteam);
        $notPossibleAttackIDs = Prereq::all()->whereNotIn('asset_id',$assets->pluck('id')); //attackIDs you have prereqs for
        $notPossibleBlueAttackIDs = Prereq::all()->whereIn('asset_id',$targetAssets->pluck('id')); //attackIDs you can do against blue
        $possibleAttacks = Attack::all()->whereNotIn('id',$notPossibleAttackIDs->pluck('attack_id')); //attacks you have prereqs for
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
        if($assetNames == null){
            $redteam = Team::find(Auth::user()->redteam);
            $error = "no-asset-selected";
            return view('redteam.store')->with(compact('assets','error', 'redteam'));
        }
        $totalCost = 0;
        foreach($assetNames as $assetName){
            $asset = Asset::all()->where('name','=',$assetName)->first();
            if($asset == null){
                throw new Exception("invalid-asset-name");
            }
            $totalCost += ($asset->purchase_cost * $sellRate);
        }
        $redteam = Team::find(Auth::user()->redteam);
        if($redteam == null){
            throw new Exception("invalid-team-selected");
        }
        foreach($assetNames as $asset){
            //add asset to inventory and charge team
            $assetId = substr(Asset::all()->where('name','=',$asset)->pluck('id'), 1, 1);
            $currAsset = Inventory::all()->where('team_id','=',Auth::user()->redteam)->where('asset_id','=', $assetId)->first();
            if($currAsset == null){
                throw new Exception("do-not-own-asset");
            }else{
                $currAsset->quantity -= 1;
                if($currAsset->quantity == 0){
                    Inventory::destroy(substr($currAsset->pluck('id'),1,1));
                }else{
                    $currAsset->update();
                }
                $redteam->balance += $totalCost;
                $redteam->update();
                return view('redteam.store')->with(compact('redteam', 'assets'));
            }
        }//end sell
    }

    public function storeInventory(){
        $redteam = Team::find(Auth::user()->redteam);
        $inventory = Inventory::all()->where('team_id','=', Auth::user()->redteam);
        $assets = Asset::all()->where('blue', '=', 0)->where('buyable', '=', 1);
        return view('redteam.store')->with(compact('redteam', 'assets', 'inventory'));
    }

    public function buy(request $request){
        $assetNames = $request->input('results');
        $assets = Asset::all()->where('blue', '=', 0)->where('buyable', '=', 1);
        if($assetNames == null){
            $redteam = Team::find(Auth::user()->redteam);
            $assets = Asset::all()->where('blue', '=', 0)->where('buyable', '=', 1);
            $error = "no-asset-selected";
            return view('redteam.store')->with(compact('assets','error','redteam'));
        }
        $totalCost = 0;
        foreach($assetNames as $assetName){
            $asset = Asset::all()->where('name','=',$assetName)->first();
            if($asset == null){
                throw new Exception("invalid-asset-name");
            }
            $totalCost += $asset->purchase_cost;
        }
        $redteam = Team::find(Auth::user()->redteam);
        if($redteam == null){
            throw new Exception("invalid-team-selected");
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
                $currAsset = new Inventory();
                $currAsset->team_id = Auth::user()->redteam;
                $currAsset->asset_id = $assetId;
                $currAsset->quantity = 1;
                $currAsset->save();
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
        if($request->name == "") return view('redteam.create'); 
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
            throw new Exception("TeamDoesNotExist");
        }
        $id = substr($team->pluck('id'), 1, 1);
        Team::destroy($id);
        return view('home');
    }
}
