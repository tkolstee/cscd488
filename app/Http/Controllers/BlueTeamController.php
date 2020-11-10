<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Inventory;
use View;
use Auth;
use Exception;


class BlueTeamController extends Controller {

    public function page($page, Request $request) {

        $blueteam = Team::find(Auth::user()->blueteam);
        switch ($page) {
            case 'home': return view('blueteam.home')->with('blueteam',$blueteam); break;
            case 'planning': return view('blueteam.planning')->with('blueteam',$blueteam); break;
            case 'status': return view('blueteam.status')->with('blueteam',$blueteam); break;
            case 'store': return $this->store();
            case 'training': return view('blueteam.training')->with('blueteam',$blueteam); break;
            case 'create': return $this->create($request); break;
            case 'join': return $this->join($request); break;
            case 'buy': return $this->buy($request); break;
        }

    }
 
    public function buy(request $request){
        $assetNames = $request->input('results');
        if($assetNames == null){
            $blueteam = Team::find(Auth::user()->blueteam);
            $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
            $error = "no-asset-selected";
            return view('blueteam.store')->with(compact('assets','error', 'blueteam'));
        }
        $totalCost = 0;
        foreach($assetNames as $assetName){
            $asset = Asset::all()->where('name','=',$assetName)->first();
            if($asset == null){
                throw new Exception("invalid-asset-name");
            }
            $totalCost += $asset->purchase_cost;
        }
        $blueteam = Team::find(Auth::user()->blueteam);
        if($blueteam == null){
            throw new Exception("invalid-team-selected");
        }
        if($blueteam->balance < $totalCost){
            $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
            $error = "not-enough-money";
            return view('blueteam.store')->with(compact('assets','error'));
        }
        foreach($assetNames as $asset){
            //add asset to inventory and charge team
            $assetId = substr(Asset::all()->where('name','=',$asset)->pluck('id'), 1, 1);
            $currAsset = Inventory::all()->where(['team_id','=',Auth::user()->blueteam],['asset_id','=', $assetId]);
            if($currAsset->isEmpty()){
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
        $blueteam->balance -= $totalCost;
        $blueteam->update();
        return view('blueteam.home')->with('blueteam', $blueteam);
    }

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
        if($blueteam->isEmpty()) throw new Exception("TeamDoesNotExist");
        $user->blueteam = substr($blueteam->pluck('id'), 1, 1);
        $user->update();
        return view('blueteam.home')->with('blueteam',$blueteam);
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
        $user->update();
        return view('blueteam.home')->with('blueteam',$blueteam);
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
