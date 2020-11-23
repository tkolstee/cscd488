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

        try{
            $blueteam = Team::find(Auth::user()->blueteam);
        }catch(TeamNotFoundException $e){
            $blueteam = null;
        }
        if($blueteam != null){
            //methods available while not on a turn
            if($page == 'startturn') return $this->startTurn(); //Testing Purposes
            if($page == 'settings') return $this->settings($request);
            $team_id = Auth::user()->blueteam;
            $blueteam = Blueteam::get($team_id);
            if($blueteam == null){
                $blueteam = Blueteam::create($team_id);
            }
            $turn = $blueteam->turn_taken;
            if($turn == 1){
                $endTime = Setting::get('turn_end_time');
                return $this->home()->with(compact('turn', 'endTime'));
            }
            //logged in with blueteam options
            switch ($page) {
                case 'home': return $this->home(); break;
                case 'planning': return view('blueteam.planning')->with('blueteam',$blueteam); break;
                case 'status': return view('blueteam.status')->with('blueteam',$blueteam); break;
                case 'store': return $this->store();
                case 'training': return view('blueteam.training')->with('blueteam',$blueteam); break;
                case 'buy': return $this->buy($request); break;
                case 'storeinventory': return $this->storeInventory(); break;
                case 'sell': return $this->sell($request); break;
                case 'endturn': return $this->endTurn(); break;
                case 'changename': return $this->changeName($request); break;
                case 'leaveteam': return $this->leaveTeam($request); break;
                default: return $this->home(); break;
            }
        }
        //no blue team options
        switch ($page) {
            case 'home': return $this->home(); break;
            case 'create': return $this->create($request); break;
            case 'join': return $this->join($request); break;
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
        try{
            Team::get($request->name);
        }
        catch(TeamNotFoundException $e){
            $team = Auth::user()->getBlueTeam();
            $team->setName($request->name);
        }
            $error = "name-taken";
            return $this->settings($request)->with(compact('error'));
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
        Auth::user()->setTurnTaken(0);
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
                $asset = Asset::get($assetName);
                $success = $blueteam->sellAsset($asset);
                if (!$success) {
                    $error = "not-enough-owned-".$asset;
                    $key = array_search($assetName, $sellCart);
                    unset($sellCart[$key]);
                    session(['sellCart' => $sellCart]);
                    return $this->store()->with(compact('error'));
                }
                $key = array_search($assetName, $sellCart);
                unset($sellCart[$key]);
            }
        }
        session(['sellCart' => null]);

        $buyCart = session('buyCart');
        if (!empty($buyCart)){
            foreach($buyCart as $assetName){
                $asset = Asset::get($assetName);
                $success = $blueteam->buyAsset($asset);
                if (!$success){
                    $error = "not-enough-money";
                    $key = array_search($assetName, $buyCart);
                    unset($buyCart[$key]);
                    session(['buyCart' => $buyCart]);
                    return $this->store()->with(compact('error'));
                }
                $key = array_search($assetName, $buyCart);
                unset($buyCart[$key]);
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
        if($assetNames == null){
            $error = "no-asset-selected";
            return $this->store()->with(compact('error'));
        }
        $sellCart = session('sellCart');
        foreach($assetNames as $asset){
            $actAsset = Asset::get($asset);
            $sellCart[] = $asset;
        }
        session(['sellCart' => $sellCart]);
        return $this->store();
    }//end sell

    public function storeInventory(){
        $inventory = Auth::user()->getBlueTeam()->inventories();
        return $this->store()->with(compact('inventory'));
    }

    public function buy(request $request){
        $assetNames = $request->input('results');
        $blueteam = Auth::user()->getBlueTeam();
        if($assetNames == null){
            $error = "no-asset-selected";
            return $this->store()->with(compact('error'));
        }
        $blueteam->balance = 1000; $blueteam->update(); //DELETE THIS IS FOR TESTING PURPOSES
        $buyCart = session('buyCart');
        foreach($assetNames as $asset){
            $actAsset = Asset::get($asset);
            $buyCart[] = $asset;
        }
        session(['buyCart' => $buyCart]);
        return $this->store();
    }

    public function store(){
        $blueteam = Auth::user()->getBlueTeam();
        try{
            $assets = Asset::getBuyableBlue();
        }catch(AssetNotFoundException $e){
            $assets = null;
        }
        return view('blueteam.store')->with(compact('blueteam', 'assets'));
    }

    public function join(request $request){
        if($request->result == ""){
            try{
                $blueteams = Team::getBlueTeams();
            }catch(TeamNotFoundException $e){
                $blueteams = null;
            }
            return view('blueteam.join')->with(compact('blueteams'));
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
        $team = Team::get($request->name);
        Auth::user()->deleteTeam($team);
        return view('home');
    }

}
