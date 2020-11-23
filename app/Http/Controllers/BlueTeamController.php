<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Asset;
use App\Models\Blueteam;
use App\Models\Setting;
use Auth;
use App\Exceptions\AssetNotFoundException;
use App\Exceptions\TeamNotFoundException;
use Exception;

class BlueTeamController extends Controller {

    public function page($page, Request $request) {

        $blueteam = Team::find(Auth::user()->blueteam);
        if($blueteam != null){
            //methods available while not on a turn
            if($page == 'startturn') return $this->startTurn(); //Testing Purposes
            if($page == 'settings') return $this->settings($request);
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
            case 'endturn': return $this->endTurn(); break;
            case 'changename': return $this->changeName($request); break;
            case 'leaveteam': return $this->leaveTeam($request); break;
            default: return $this->home(); break;
        }

    }
 
    public function leaveTeam(request $request){
        if($request->result == "stay"){
            return $this->settings($request);
        }
        $user = Auth::user();
        $user->leaveBlueTeam();
        return $this->home();
    }

    public function changeName(request $request){
        if(!Team::all()->where('name','=',$request->name)->isEmpty()){
            $error = "name-taken";
            return $this->settings($request)->with(compact('error'));
        }
        $team = Auth::user()->getBlueTeam();
        $success = $team->setName($request->name);
        if($success){
            return $this->settings($request);
        }else{
            throw new Exception("Name unchanged");
        }
    }

    public function settings(request $request){
        $changeName = false;
        $leaveTeam = false;
        if($request->changeNameBtn == 1){
            $changeName = true;
        }
        if($request->leaveTeamBtn == 1){
            $leaveTeam = true;
        }
        $blueteam = Auth::user()->getBlueTeam();
        $leader = $blueteam->leader();
        $members = $blueteam->members();
        return view('blueteam/settings')->with(compact('blueteam','leader','members','changeName','leaveTeam'));
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
        $blueteam = Auth::user()->getBlueTeam();
        $totalBalance = 0;
        $sellCart = session('sellCart');
        if (!empty($sellCart)){
            foreach($sellCart as $assetName){
                $asset = Asset::all()->where('name', '=', $assetName)->first();
                if($asset == null) { throw new AssetNotFoundException();}
                $success = $blueteam->sellAsset($asset);
                if (!$success) {
                    $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
                    $error = "not-enough-owned-".$asset;
                    session(['sellCart' => null]);
                    return view('blueteam.store')->with(compact('blueteam','assets','error'));
                }
            }
        }
        session(['sellCart' => null]);

        $buyCart = session('buyCart');
        if (!empty($buyCart)){
            foreach($buyCart as $assetName){
                $asset = Asset::all()->where('name', '=', $assetName)->first();
                if($asset == null) { throw new AssetNotFoundException();}
                $success = $blueteam->buyAsset($asset);
                if (!$success){
                    $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
                    $error = "not-enough-money";
                    session(['buyCart' => null]);
                    return view('blueteam.store')->with(compact('assets','error','blueteam'));
                }
            }
        }
        session(['buyCart' => null]);
        //update turn stuff
        $blueteam = Blueteam::all()->where('team_id','=',$blueteam->id)->first();
        $blueteam->turn_taken = 1;
        $blueteam->update();
        $turn = 1;
        $endTime = Setting::get('turn_end_time');
        return $this->home()->with(compact('turn', 'endTime'));
    }

    public function home(){
        try {
            $blueteam = Auth::user()->getBlueTeam();
        }
        catch (TeamNotFoundException $e) {
            return view('blueteam.home');
        }
        $leader = $blueteam->leader();
        $members = $blueteam->members();
        $turn = 0;
        return  view('blueteam.home')->with(compact('blueteam','leader','members', 'turn'));
    }

    public function sell(request $request){
        $blueteam = Auth::user()->getBlueTeam();
        $assetNames = $request->input('results');
        $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
        if($assetNames == null){
            $error = "no-asset-selected";
            return view('blueteam.store')->with(compact('assets','error', 'blueteam'));
        }
        $sellCart = session('sellCart');
        foreach($assetNames as $asset){
            $actAsset = Asset::all()->where('name','=',$asset)->first();
            if($actAsset == null){ throw new AssetNotFoundException();}
            $sellCart[] = $asset;
        }
        session(['sellCart' => $sellCart]);
        return $this->store();
    }//end sell

    public function storeInventory(){
        $blueteam = Auth::user()->getBlueTeam();
        $inventory = $blueteam->inventories();
        $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
        return view('blueteam.store')->with(compact('blueteam', 'assets', 'inventory'));
    }

    public function buy(request $request){
        $assetNames = $request->input('results');
        $blueteam = Auth::user()->getBlueTeam();
        if($assetNames == null){
            $assets = Asset::all()->where('blue', '=', 1)->where('buyable', '=', 1);
            $error = "no-asset-selected";
            return view('blueteam.store')->with(compact('assets','error', 'blueteam'));
        }
        $blueteam->balance = 1000; $blueteam->update(); //DELETE THIS IS FOR TESTING PURPOSES
        $buyCart = session('buyCart');
        foreach($assetNames as $asset){
            $actAsset = Asset::all()->where('name','=',$asset)->first();
            if($actAsset == null){ throw new AssetNotFoundException();}
            $buyCart[] = $asset;
        }
        session(['buyCart' => $buyCart]);
        return $this->store();
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
        Auth::user()->joinBlueTeam($request->result);
        return $this->home();
    }

    public function create(request $request){
        if($request->name == "") return view('blueteam.create'); 
        $this->validate($request, [
            'name' => ['required', 'unique:teams', 'string', 'max:255'],
        ]);
        Auth::user()->createBlueTeam($request->name);
        return $this->home();
    }

    public function delete(request $request){
        $team = Team::all()->where('name', '=', $request->name)->first();
        if($team == null) { throw new TeamNotFoundException();}
        Auth::user()->deleteTeam($team);
        return view('home');
    }

}
