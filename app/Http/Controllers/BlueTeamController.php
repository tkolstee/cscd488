<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Inventory;
use App\Models\User;
use View;
use Auth;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Exceptions\InventoryNotFoundException;


class BlueTeamController extends Controller {

    public function page($page, Request $request) {

        $blueteam = Team::find(Auth::user()->blueteam);
        switch ($page) {
            case 'home': return $this->home(); break;
            case 'planning': return view('blueteam.planning')->with('blueteam',$blueteam); break;
            case 'status': return view('blueteam.status')->with('blueteam',$blueteam); break;
            case 'store': return $this->store();
            case 'training': return view('blueteam.training')->with('blueteam',$blueteam); break;
            case 'create': return $this->create($request); break;
            case 'join': return $this->join($request); break;
            case 'buy': return $this->buy($request); break;
            case 'storeinventory': return $this->storeInventory(); break;
            case 'sell': return $this->sell($request); break;
        }

    }
 
    public function home(){
        $blueid = Auth::user()->blueteam;
        $blueteam = Team::find($blueid);
        if($blueid == "") return view('blueteam.home')->with(compact('blueteam'));
        $leader = User::all()->where('blueteam','=',$blueid)->where('leader','=',1)->first();
        $members = User::all()->where('blueteam','=',$blueid)->where('leader','=',0);
        return  view('blueteam.home')->with(compact('blueteam','leader','members'));
    }

    public function sell(request $request){
        //change this to proportion sell rate
        $sellRate = 1;
        $blueteam = Team::find(Auth::user()->blueteam);
        $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
        $assetNames = $request->input('results');
        if($assetNames == null){
            $error = "no-asset-selected";
            return view('blueteam.store')->with(compact('assets','error', 'blueteam'));
        }
        else if($blueteam == null){
            throw new TeamNotFoundException();
        }
        foreach($assetNames as $assetName){
            //remove asset from inventory and pay team for each item
            $asset = Asset::all()->where('name','=',$assetName)->first();
            if ($asset == null){
                throw new AssetNotFoundException();
            }
            $currInventory = Inventory::all()->where('team_id','=',$blueteam->id)->where('asset_id','=', $asset->id)->first();
            if($currInventory == null){
                throw new InventoryNotFoundException();
            }
            $currInventory->quantity -= 1;
            if($currInventory->quantity == 0){
                Inventory::destroy($currInventory->id);
            }else{
                $currInventory->update();
            }
            $blueteam->balance += ($asset->purchase_cost)*$sellRate;
            $blueteam->update();
        }
        return view('blueteam.store')->with(compact('blueteam', 'assets'));
    }//end sell

    public function storeInventory(){
        $blueteam = Team::find(Auth::user()->blueteam);
        $inventory = Inventory::all()->where('team_id','=', Auth::user()->blueteam);
        $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
        return view('blueteam.store')->with(compact('blueteam', 'assets', 'inventory'));
    }

    public function buy(request $request){
        $assetNames = $request->input('results');
        $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
        $blueteam = Team::find(Auth::user()->blueteam);
        if($blueteam == null){
            throw new TeamNotFoundException();
        }
        if($assetNames == null){
            $error = "no-asset-selected";
            return view('blueteam.store')->with(compact('assets','error', 'blueteam'));
        }
        $totalCost = 0;
        foreach($assetNames as $assetName){
            $asset = Asset::all()->where('name','=',$assetName)->first();
            if($asset == null){
                throw new AssetNotFoundException();
            }
            $totalCost += $asset->purchase_cost;
        }
        if($blueteam->balance < $totalCost){
            $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
            $error = "not-enough-money";
            return view('blueteam.store')->with(compact('assets','error','blueteam'));
        }
        foreach($assetNames as $asset){
            //add asset to inventory and charge team
            $assetId = substr(Asset::all()->where('name','=',$asset)->pluck('id'), 1, 1);
            $currAsset = Inventory::all()->where('team_id','=',Auth::user()->blueteam)->where('asset_id','=', $assetId)->first();
            if($currAsset == null){
                Inventory::factory()->create([
                    'team_id' => $blueteam,
                    'asset_id' => $assetId,
                    'quantity' => 1,
                ]);
            }else{
                $currAsset->quantity += 1;
                $currAsset->update();
            }
        }
        $blueteam->balance -= $totalCost;
        $blueteam->update();
        return view('blueteam.store')->with(compact('blueteam', 'assets'));
    }//end buy

    public function store(){
        $blueteam = Team::find(Auth::user()->blueteam);
        $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
        return view('blueteam.store')->with(compact('blueteam', 'assets'));
    }

    public function join(request $request){
        if($request->result == ""){
            $blueteams = Team::all()->where('blue', '=', 1);
            return view('blueteam.join')->with('blueteams', $blueteams);
        }
        $user = Auth::user();
        $blueteam = Team::all()->where('name', '=', $request->result);
        if($blueteam->isEmpty()) throw new TeamNotFoundException();
        $user->blueteam = substr($blueteam->pluck('id'), 1, 1);
        $user->update();
        return $this->home();
    }

    public function create(request $request){
        if($request->name == "") return view('blueteam.create'); 
        $this->validate($request, [
            'name' => ['required', 'unique:teams', 'string', 'max:255'],
        ]);
        $blueteam = new Team();
        $blueteam->name = $request->name;
        $blueteam->balance = 0;
        $blueteam->blue = 1;
        $blueteam->reputation = 0;
        $blueteam->save();
        $user = Auth::user();
        $user->blueteam = substr(Team::all()->where('name', '=', $request->name)->pluck('id'), 1, 1);
        $user->leader = 1;
        $user->update();
        return $this->home();
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
