<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Blueteam;
use App\Models\Setting;
use View;
use Auth;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use App\Exceptions\InventoryNotFoundException;


class BlueTeamController extends Controller {

    public function page($page, Request $request) {

        if($page == 'startturn') return $this->startTurn(); //Testing Purposes
        $blueteam = Team::find(Auth::user()->blueteam);
        if($blueteam != null){
            $team_id = Auth::user()->blueteam;
            $blueteam = Blueteam::all()->where('team_id','=',$team_id)->first();
            if($blueteam == null){
                $blueteam = new Blueteam();
                $blueteam->team_id = $team_id;
                $blueteam->save();
            }
            $turn = Blueteam::all()->where('team_id','=',$team_id)->first()->turn_taken;
            if($turn == 1){
                $endTime = Setting::get('turn_end_time');
                return $this->home()->with(compact('turn', 'endTime'));
            }
        }
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
            default: return $this->home(); break;
            case 'endturn': return $this->endTurn(); break;
        }

    }
 
    public function startTurn(){ //Testing Purposes
        $teamID = Auth::user()->blueteam;
        $blueteam = Blueteam::all()->where('team_id','=',$teamID)->first();
        $blueteam->turn_taken = 0;
        $blueteam->update();
        $turn = 0;
        return $this->home()->with(compact('turn'));
    }

    public function endTurn(){
        //Buy and sell items in Session
        $cart = session('cart');
        $teamID = Auth::user()->blueteam;
        $team = Team::find($teamID);
        if($team == null){
            throw new Exception("invalid-team-selected");
        }
        $totalBalance = 0;
        if(!empty($cart)){
            $length = count($cart);
            for($i = 0; $i < $length; $i++){
                if($cart[$i] == -1){ //Selling next item
                    $sellRate = 1;
                    $i++;
                    $asset = Asset::all()->where('name','=',$cart[$i])->first();
                    if($asset == null){
                        throw new Exception("invalid-asset-name");
                    }
                    $totalBalance += ($asset->purchase_cost * $sellRate);
                }else{
                    $asset = Asset::all()->where('name','=',$cart[$i])->first();
                    if($asset == null){
                        throw new Exception("invalid-asset-name");
                    }
                    $totalBalance -= ($asset->purchase_cost);
                }
                $currentBalance = $team->balance;
                if($currentBalance + $totalBalance < 0){
                    $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
                    $error = "not-enough-money";
                    $blueteam = $team;
                    return view('blueteam.store')->with(compact('assets','error','blueteam'));
                }
            }
            for($i = 0; $i < $length; $i++){
                if($cart[$i] == -1){ //Sell
                    $i++;
                    $assetId = substr(Asset::all()->where('name','=',$cart[$i])->pluck('id'), 1, 1);
                    $currAsset = Inventory::all()->where('team_id','=',Auth::user()->blueteam)->where('asset_id','=', $assetId)->first();
                    if($currAsset == null){
                        throw new Exception("do-not-own-asset");
                    }
                    $currAsset->quantity -= 1;
                    if($currAsset->quantity == 0){
                        Inventory::destroy(substr($currAsset->pluck('id'),1,1));
                    }else{
                        $currAsset->update();
                    }
                }else{ //Buy
                    $assetId = substr(Asset::all()->where('name','=',$cart[$i])->pluck('id'), 1, 1);
                    $currAsset = Inventory::all()->where('team_id','=',Auth::user()->blueteam)->where('asset_id','=', $assetId)->first();
                    if($currAsset == null){
                        $currAsset = new Inventory();
                        $currAsset->team_id = Auth::user()->blueteam;
                        $currAsset->asset_id = $assetId;
                        $currAsset->quantity = 1;
                        $currAsset->save();
                    }else{
                        $currAsset->quantity += 1;
                        $currAsset->update();
                    }
                }
            }
        }
        //finish cart stuff
        $team->balance += $totalBalance;
        $team->update();
        session(['cart' => null]);
        //update turn stuff
        $blueteam = Blueteam::all()->where('team_id','=',$teamID)->first();
        $blueteam->turn_taken = 1;
        $blueteam->update();
        $turn = 1;
        $endTime = Setting::get('turn_end_time');
        return $this->home()->with(compact('turn', 'endTime'));
    }

    public function home(){
        $blueid = Auth::user()->blueteam;
        $blueteam = Team::find($blueid);
        if($blueid == ""){ return view('blueteam.home')->with(compact('blueteam'));}
        $leader = User::all()->where('blueteam','=',$blueid)->where('leader','=',1)->first();
        $members = User::all()->where('blueteam','=',$blueid)->where('leader','=',0);
        $turn = 0;
        return  view('blueteam.home')->with(compact('blueteam','leader','members', 'turn'));
    }

    public function sell(request $request){
        //change this to proportion sell rate
        $sellRate = 1;
        $blueteam = Team::find(Auth::user()->blueteam);
        $assetNames = $request->input('results');
        $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
        $assetNames = $request->input('results');
        if($assetNames == null){
            $error = "no-asset-selected";
            return view('blueteam.store')->with(compact('assets','error', 'blueteam'));
        }
        $blueteam = Team::find(Auth::user()->blueteam);
        if($blueteam == null){
            throw new Exception("invalid-team-selected");
        }
        $cart = session('cart');
        $alreadySold = [];
            if($cart != null){
                $nextIsSell = 0;
                foreach($cart as $item){
                    if($nextIsSell == 1){
                        $alreadySold[] = $item;
                    }
                    if($item == -1){
                        $nextIsSell = 1;
                    }else{
                        $nextIsSell = 0;
                    }
                    $newCart[] = $item;
                }
            }
        foreach($assetNames as $asset){
            $assetId = substr(Asset::all()->where('name','=',$asset)->pluck('id'), 1, 1);
            $currAsset = Inventory::all()->where('team_id','=',Auth::user()->blueteam)->where('asset_id','=', $assetId)->first();
            if($currAsset == null){
                throw new Exception("do-not-own-asset");
            }
            $actAsset = Asset::find($assetId);
            if($actAsset == null){
                throw new Exception("invalid-asset-name");
            }
            foreach($alreadySold as $itemSold){
                if($itemSold == $asset){
                    $currAsset->quantity--;
                    if($currAsset->quantity == 0){
                        $error = "not-enough-owned-".$asset;
                        return view('blueteam.store')->with(compact('blueteam','assets','error'));
                    }
                }
            }
            $newCart[] = -1;
            $newCart[] = $asset;
            session(['cart' => $newCart]);
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
        $blueteam = Team::find(Auth::user()->blueteam);
        if($blueteam == null){
            throw new Exception("invalid-team-selected");
        }
        $blueteam->balance = 1000; $blueteam->update(); //DELETE THIS IS FOR TESTING PURPOSES
        $cart = session('cart');
            if($cart != null){
                foreach($cart as $item){
                    $newCart[] = $item;
                }
            }
        foreach($assetNames as $asset){
            $actAsset = Asset::all()->where('name','=',$asset)->first();
            if($actAsset == null){
                throw new Exception("invalid-asset-name");
            }
            $newCart[] = $asset;
            session(['cart' => $newCart]);
        }
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
        $team = new Team();
        $team->name = $request->name;
        $team->balance = 0;
        $team->blue = 1;
        $team->reputation = 0;
        $team->save();
        $blueteam = new Blueteam();
        $teamID = substr(Team::all()->where('name', '=', $request->name)->pluck('id'), 1, 1);
        $blueteam->team_id = $teamID;
        $blueteam->save();
        $user = Auth::user();
        $user->blueteam = $teamID;
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
